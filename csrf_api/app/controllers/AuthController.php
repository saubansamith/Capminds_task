<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/JWT.php';

class AuthController {

    /* ================= REGISTER ================= */

    public static function register() {

        $data = $GLOBALS['request_body'];

        if (!isset($data['name'], $data['email'], $data['password'])) {
            Response::error("All fields are required");
        }

        $userModel = new User();

        if ($userModel->findByEmail($data['email'])) {
            Response::error("Email already exists");
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $userModel->create($data['name'], $data['email'], $hashedPassword);

        Response::success("User registered successfully");
    }

    /* ================= LOGIN ================= */

    public static function login() {

        $data = $GLOBALS['request_body'];

        if (!isset($data['email'], $data['password'])) {
            Response::error("Email and password required");
        }

        $userModel = new User();
        $user = $userModel->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::error("Invalid credentials", 401);
        }

        require_once __DIR__ . '/../core/Database.php';
        $conn = Database::connect();

        $accessExpiry  = (int)($_ENV['ACCESS_TOKEN_EXPIRY'] ?? 90);
        $refreshExpiry = (int)($_ENV['REFRESH_TOKEN_EXPIRY'] ?? 172800);

        /* -------- CREATE ACCESS TOKEN -------- */

        $payload = [
            "user_id" => $user['id'],
            "email"   => $user['email'],
            "iat"     => time(),
            "exp"     => time() + $accessExpiry
        ];

        $accessToken = JWT::generate($payload);

        /* -------- creating a REFRESH TOKEN -------- */

        $refreshToken = bin2hex(random_bytes(40));
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
        $insert->bind_param("iss", $user['id'], $refreshToken, $refreshTokenExpiry);
        $insert->execute();

        /* -------- SET COOKIE -------- */

        setcookie(
            "refresh_token",
            $refreshToken,
            [
                'expires'  => time() + $refreshExpiry,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Strict' // prevent csrf attack
            ]
        );
        require_once __DIR__ . '/../helpers/Csrf.php';

        /* -------- GENERATE CSRF TOKEN -------- */

        $csrfToken = Csrf::generate();

        /* -------- STORE IN SESSION -------- */

        Csrf::storeInSession($csrfToken);

        /* -------- RESPONSE -------- */

        Response::success("Login successful", [
            "access_token" => $accessToken,
            "expires_in"   => $accessExpiry,
            "csrf_token"   => $csrfToken
        ]);



        Response::success("Login successful", [
            "access_token" => $accessToken,
            "expires_in"   => $accessExpiry
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

        // Check refresh token in table exist
        $stmt = $conn->prepare("
            SELECT user_id, expires_at
            FROM refresh_tokens
            WHERE refresh_token = ?
        ");
        $stmt->bind_param("s", $oldRefreshToken);
        $stmt->execute();

        $result = $stmt->get_result();
        $tokenData = $result->fetch_assoc();

        if (!$tokenData) {
            Response::error("Invalid refresh token", 401);
        }

        if (strtotime($tokenData['expires_at']) < time()) {
            Response::error("Refresh token expired. Login again.", 401);
        }

        $userId = $tokenData['user_id'];

        // Get user email
        $userStmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $user = $userResult->fetch_assoc();

        $accessExpiry  = (int)($_ENV['ACCESS_TOKEN_EXPIRY'] ?? 90);
        $refreshExpiry = (int)($_ENV['REFRESH_TOKEN_EXPIRY'] ?? 172800);

        /* -------- ROTATE REFRESH TOKEN -------- */

        $newRefreshToken = bin2hex(random_bytes(40));
        $newRefreshExpiry = date('Y-m-d H:i:s', time() + $refreshExpiry);

        // Delete old token
        $deleteOld = $conn->prepare("DELETE FROM refresh_tokens WHERE refresh_token = ?");
        $deleteOld->bind_param("s", $oldRefreshToken);
        $deleteOld->execute();

        // Insert new token
        $insert = $conn->prepare("
            INSERT INTO refresh_tokens (user_id, refresh_token, expires_at)
            VALUES (?, ?, ?)
        ");
        $insert->bind_param("iss", $userId, $newRefreshToken, $newRefreshExpiry);
        $insert->execute();

        /* -------- UPDATE COOKIE -------- */

        setcookie(
            "refresh_token",
            $newRefreshToken,
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
