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

        $payload = [
            "user_id" => $user['id'],
            "email"   => $user['email'],
            "iat"     => time(),
            "exp"     => time() + $_ENV['JWT_EXPIRY']
        ];

        $token = JWT::generate($payload);

        Response::success("Login successful", ["token" => $token]);
    }
}
