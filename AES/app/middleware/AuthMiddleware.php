<?php
require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../helpers/Response.php';

class AuthMiddleware {

    public static function handle() {

        // Get Authorization header in all possible ways.
        $authHeader = null;
    
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } else {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            }
        }
    
        if (!$authHeader) {
            Response::error('Authorization header missing', 401);
        }
    
        // Expecting Bearer tokn
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            Response::error('Invalid Authorization format', 401);
        }
    
        $token = $matches[1];
    
        $payload = JWT::verify($token);
    
        if (!$payload) {
            Response::error('Invalid or expired token', 401);
        }
    
        $_REQUEST['user'] = $payload;
    }
    
}

