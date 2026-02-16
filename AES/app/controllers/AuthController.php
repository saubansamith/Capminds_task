<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../helpers/AES.php';
require_once __DIR__ . '/../helpers/BlindIndex.php';


class AuthController {

    /* ================= REGISTER ================= */

    public static function register() {

        $data = $GLOBALS['request_body'];

        if (!isset($data['name'], $data['email'], $data['password'])) {
            Response::error("All fields are required");
        }

        $userModel = new User();

       /* -------- NORMALIZE EMAIL -------- */
        $emailNormalized = strtolower(trim($data['email']));

        /* -------- GENERATE HASH FOR SEARCH -------- */
        $emailHash = BlindIndex::emailHash($emailNormalized);

        /* -------- CHECK DUPLICATE USING HASH -------- */
        if ($userModel->findByEmailHash($emailHash)) {
            Response::error("Email already exists");
        }

        /* -------- ENCRYPT EMAIL FOR STORAGE -------- */
        $emailEncrypted = AES::encrypt($emailNormalized);

        /* -------- PASSWORD HASH -------- */
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        /* -------- SAVE USER -------- */
        $userModel->create(
            $data['name'],
            $emailEncrypted,
            $emailHash,
            $hashedPassword
        );

        Response::success("User registered successfully");
    }

    /* ================= LOGIN ================= */

    public static function login() {

        $data = $GLOBALS['request_body'];
    
        if (!isset($data['email'], $data['password'])) {
            Response::error("Email and password required");
        }
    
        $userModel = new User();
    
        /* Normalize Email */
        $emailNormalized = strtolower(trim($data['email']));
    
        /* Create Blind Index Hash */
        $emailHash = BlindIndex::emailHash($emailNormalized);
    
        /* Find user using HASH (NOT plain email) */
        $user = $userModel->findByEmailHash($emailHash);
    
        /* Validate Password */
        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::error("Invalid credentials", 401);
        }
    
        /* Decrypt email only after user verified */
        $userEmailPlain = AES::decrypt($user['email_encrypted']);
    
        require_once __DIR__ . '/../core/Database.php';
        $conn = Database::connect();
    
        $accessExpiry  = (int)($_ENV['ACCESS_TOKEN_EXPIRY'] ?? 60);
        $refreshExpiry = (int)($_ENV['REFRESH_TOKEN_EXPIRY'] ?? 172800);
    
        /* -------- CREATE ACCESS TOKEN -------- */

        $payload = [
            "user_id" => $user['id'],
            "email" => $userEmailPlain,
            "iat"     => time(),
            "exp"     => time() + $accessExpiry
        ];

        $accessToken = JWT::generate($payload);

        /* -------- creating a REFRESH TOKEN -------- */

        $refreshTokenPlain = bin2hex(random_bytes(40));
        $refreshTokenHash  = password_hash($refreshTokenPlain, PASSWORD_DEFAULT);

        $refreshTokenExpiry = date('Y-m-d H:i:s', time() + $refreshExpiry);

        // Delete old tokens for this user
        $deleteOld = $conn->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
        $deleteOld->bind_param("i", $user['id']);
        $deleteOld->execute();

        // Insert new refresh token
        $insert = $conn->prepare("
            INSERT INTO refresh_tokens (user_id, refresh_token, expires_at)
            VALUES (?, ?, ?)
        ");
        $insert->bind_param("iss", $user['id'], $refreshTokenHash, $refreshTokenExpiry);
        $insert->execute();

        /* -------- SET COOKIE -------- */

        setcookie(
            "refresh_token",
            $refreshTokenPlain,  
            [
                'expires'  => time() + $refreshExpiry,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );

        /* -------- CSRF -------- */

        require_once __DIR__ . '/../helpers/Csrf.php';

        $csrfToken = Csrf::generate();

        /* -------- RESPONSE -------- */

        Response::success("Login successful", [
            "access_token" => $accessToken,
            "expires_in"   => $accessExpiry,
            "csrf_token"   => $csrfToken
        ]);
    }

    /* REFRESH  */

    public static function refresh() {

        if (!isset($_COOKIE['refresh_token'])) {
            Response::error("Refresh token missing", 401);
        }

        require_once __DIR__ . '/../core/Database.php';
        $conn = Database::connect();

        $oldRefreshToken = $_COOKIE['refresh_token'];

        /* -------- GET ALL TOKENS -------- */

        $stmt = $conn->prepare("
            SELECT user_id, refresh_token, expires_at
            FROM refresh_tokens
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $validToken = null;

        while ($row = $result->fetch_assoc()) {

            if (password_verify($oldRefreshToken, $row['refresh_token'])) {

                if (strtotime($row['expires_at']) < time()) {
                    Response::error("Refresh token expired. Login again.", 401);
                }

                $validToken = $row;
                break;
            }
        }

        if (!$validToken) {
            Response::error("Invalid refresh token", 401);
        }

        $userId = $validToken['user_id'];


        // Get user email
        $userStmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $user = $userResult->fetch_assoc();

        $accessExpiry  = (int)($_ENV['ACCESS_TOKEN_EXPIRY'] ?? 60);
        $refreshExpiry = (int)($_ENV['REFRESH_TOKEN_EXPIRY'] ?? 172800);

        /* -------- ROTATE REFRESH TOKEN -------- */

        $newRefreshTokenPlain = bin2hex(random_bytes(40));
        $newRefreshTokenHash  = password_hash($newRefreshTokenPlain, PASSWORD_DEFAULT);

        $newRefreshExpiry = date('Y-m-d H:i:s', time() + $refreshExpiry);

        /* Delete old token */
        $deleteOld = $conn->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
        $deleteOld->bind_param("i", $userId);
        $deleteOld->execute();

        /* Insert new token */
        $insert = $conn->prepare("
            INSERT INTO refresh_tokens (user_id, refresh_token, expires_at)
            VALUES (?, ?, ?)
        ");
        $insert->bind_param("iss", $userId, $newRefreshTokenHash, $newRefreshExpiry);
        $insert->execute();

        /* Update Cookie */
        setcookie(
            "refresh_token",
            $newRefreshTokenPlain,
            [
                'expires'  => time() + $refreshExpiry,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );


        /* -------- CREATE NEW ACCESS TOKEN -------- */

        $payload = [
            "user_id" => $userId,
            "email"   => $user['email'],
            "iat"     => time(),
            "exp"     => time() + $accessExpiry
        ];

        $newAccessToken = JWT::generate($payload);

        Response::success("Tokens refreshed", [
            "access_token" => $newAccessToken,
            "expires_in"   => $accessExpiry
        ]);
    }
}
