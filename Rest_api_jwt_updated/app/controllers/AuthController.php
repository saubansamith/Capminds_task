<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/JWT.php';

class AuthController {

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
    
        // ✅ 1. Create Access Token (15 mins)
        $payload = [
            "user_id" => $user['id'],
            "email"   => $user['email'],
            "iat"     => time(),
            "exp"     => time() + (15 * 60)
        ];
    
        $accessToken = JWT::generate($payload);
    
        // ✅ 2. Create Refresh Token (random string)
        require_once __DIR__ . '/../core/Database.php';
    
        $refreshToken = bin2hex(random_bytes(40));
        $expiry = date('Y-m-d H:i:s', strtotime('+2 days'));
    
        $conn = Database::connect();
        $stmt = $conn->prepare("
            UPDATE users 
            SET refresh_token=?, refresh_token_expiry=? 
            WHERE id=?
        ");
        $stmt->bind_param("ssi", $refreshToken, $expiry, $user['id']);
        $stmt->execute();
    
        // ✅ 3. Store refresh token in HttpOnly cookie
        setcookie(
            "refresh_token",
            $refreshToken,
            time() + (2 * 24 * 60 * 60),
            "/",
            "",
            false,
            true
        );
    
        // ✅ 4. Send access token in response
        Response::success("Login successful", [
            "access_token" => $accessToken,
            "expires_in" => 900
        ]);
    }
        public static function refresh() {

            if (!isset($_COOKIE['refresh_token'])) {
                Response::error("Refresh token missing", 401);
            }
        
            $refreshToken = $_COOKIE['refresh_token'];
        
            require_once __DIR__ . '/../core/Database.php';
            $conn = Database::connect();
        
            // Check token in DB
            $stmt = $conn->prepare("
                SELECT id, email, refresh_token_expiry 
                FROM users 
                WHERE refresh_token=?
            ");
            $stmt->bind_param("s", $refreshToken);
            $stmt->execute();
        
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        
            if (!$user) {
                Response::error("Invalid refresh token", 401);
            }
        
            // Check expiry
            if (strtotime($user['refresh_token_expiry']) < time()) {
                Response::error("Refresh token expired. Login again.", 401);
            }
        
            // ✅ Create new Access Token
            $payload = [
                "user_id" => $user['id'],
                "email"   => $user['email'],
                "iat"     => time(),
                "exp"     => time() + (15 * 60)
            ];
        
            $newAccessToken = JWT::generate($payload);
        
            Response::success("New access token generated", [
                "access_token" => $newAccessToken,
                "expires_in" => 900
            ]);
        }
        
}
