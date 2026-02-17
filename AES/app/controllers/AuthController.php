<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../helpers/AES.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/BlindIndex.php';
require_once __DIR__ . '/../core/Database.php';

class AuthController {

    /* ================= REGISTER ================= */

    public static function register() {

        $data = $GLOBALS['request_body'];

        if (!isset($data['name'], $data['email'], $data['password'])) {
            Response::error("All fields are required");
        }

        $userModel = new User();

        // Normalize email
        $emailNormalized = strtolower(trim($data['email']));

        // Generate blind index hash
        $emailHash = BlindIndex::emailHash($emailNormalized);

        // Check duplicate using hash
        if ($userModel->findByEmailHash($emailHash)) {
            Response::error("Email already exists");
        }

        // Encrypt email for storage
        $emailEncrypted = AES::encrypt($emailNormalized);

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Save user
        $userModel->create(
            trim($data['name']),
            $emailEncrypted,
            $emailHash,
            $hashedPassword
        );

        Response::success("User registered successfully");
    }

    /* ================= LOGIN ================= */

    public static function login() {

        $data = $GLOBALS['request_body'];

        if (
            empty($data['email']) ||
            empty($data['password'])
        ) {
            Response::error("Email and password required", 400);
        }
        

        $userModel = new User();

        // Normalize email
        $emailNormalized = strtolower(trim($data['email']));

        // Generate blind index
        $emailHash = BlindIndex::emailHash($emailNormalized);

        // Find user by hash
        $user = $userModel->findByEmailHash($emailHash);

        if (
            !$user ||
            empty($user['password']) ||
            !password_verify($data['password'], $user['password'])
        ) {
            Response::error("Invalid credentials", 401);
        }
        

        // Decrypt email after verification
        $userEmailPlain = AES::decrypt($user['email_encrypted']);

        $conn = Database::connect();

        $accessExpiry  = (int)($_ENV['ACCESS_TOKEN_EXPIRY'] ?? 60);
        $refreshExpiry = (int)($_ENV['REFRESH_TOKEN_EXPIRY'] ?? 172800);

        /* -------- CREATE ACCESS TOKEN -------- */

        $payload = [
            "user_id" => $user['id'],
            "email"   => $userEmailPlain,
            "iat"     => time(),
            "exp"     => time() + $accessExpiry
        ];

        $accessToken = JWT::generate($payload);

        /* -------- CREATE REFRESH TOKEN -------- */

        $refreshTokenPlain = bin2hex(random_bytes(40));
        $refreshTokenHash  = password_hash($refreshTokenPlain, PASSWORD_DEFAULT);
        $refreshTokenExpiry = date('Y-m-d H:i:s', time() + $refreshExpiry);

        // Delete old tokens
        $deleteOld = $conn->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
        $deleteOld->bind_param("i", $user['id']);
        $deleteOld->execute();

        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userAgentHash = hash('sha256', $userAgent);

        // Insert new refresh token
        $insert = $conn->prepare("
        INSERT INTO refresh_tokens 
        (user_id, refresh_token, expires_at, user_agent_hash, last_ip, risk_score)
        VALUES (?, ?, ?, ?, ?, 0)");

        $insert->bind_param(
            "issss",
            $user['id'],
            $refreshTokenHash,
            $refreshTokenExpiry,
            $userAgentHash,
            $currentIp
        );

        $insert->execute();

        // Set secure HttpOnly cookie
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

        $csrfToken = Csrf::generate();

        Response::success("Login successful", [
        "access_token" => $accessToken,
        "expires_in"   => $accessExpiry,
        "csrf_token"   => $csrfToken
    ]);

    }

    /* ================= REFRESH ================= */

    public static function refresh() {

        if (!isset($_COOKIE['refresh_token'])) {
            Response::error("Refresh token missing", 401);
        }

        $conn = Database::connect();
        $oldRefreshToken = $_COOKIE['refresh_token'];

        // Fetch all refresh tokens
        $stmt = $conn->prepare("
            SELECT user_id, refresh_token, expires_at,
                   user_agent_hash, last_ip, risk_score
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

        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $currentUserAgentHash = hash('sha256', $userAgent);

        $riskScore = (int)$validToken['risk_score'];

        // Check IP change
        if ($validToken['last_ip'] !== $currentIp) {
            $riskScore += 1;
        }

        // Check User-Agent change
        if ($validToken['user_agent_hash'] !== $currentUserAgentHash) {
            $riskScore += 2;
        }

        if ($riskScore >= 4) {

            // Delete refresh token
            $delete = $conn->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
            $delete->bind_param("i", $userId);
            $delete->execute();

            setcookie("refresh_token", "", time() - 3600, "/");

            Response::error("Suspicious activity detected. Please login again.", 401);
        }

        // Get encrypted email
        $userStmt = $conn->prepare("SELECT email_encrypted FROM users WHERE id = ?");
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $user = $userStmt->get_result()->fetch_assoc();

        if (!$user) {
            Response::error("User not found", 404);
        }

        // Decrypt email
        $email = AES::decrypt($user['email_encrypted']);

        $accessExpiry  = (int)($_ENV['ACCESS_TOKEN_EXPIRY'] ?? 60);
        $refreshExpiry = (int)($_ENV['REFRESH_TOKEN_EXPIRY'] ?? 172800);

        /* -------- ROTATE REFRESH TOKEN -------- */

        $newRefreshTokenPlain = bin2hex(random_bytes(40));
        $newRefreshTokenHash  = password_hash($newRefreshTokenPlain, PASSWORD_DEFAULT);
        $newRefreshExpiry = date('Y-m-d H:i:s', time() + $refreshExpiry);

        // Delete old
        $deleteOld = $conn->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
        $deleteOld->bind_param("i", $userId);
        $deleteOld->execute();

        // Insert new
        $insert = $conn->prepare("
        INSERT INTO refresh_tokens 
        (user_id, refresh_token, expires_at, user_agent_hash, last_ip, risk_score)
        VALUES (?, ?, ?, ?, ?, ?)");

        $insert->bind_param(
            "issssi",
            $userId,
            $newRefreshTokenHash,
            $newRefreshExpiry,
            $currentUserAgentHash,
            $currentIp,
            $riskScore
        );

        $insert->execute();

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
            "email"   => $email,
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
