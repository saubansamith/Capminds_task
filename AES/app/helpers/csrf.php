<?php

class Csrf {

    private static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function generate() {
        self::startSession(); // to check session is started before storing the token

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;

        return $token;
    }
// to store session manually if req
    // public static function storeInSession($token) {
    //     self::startSession();
    //     $_SESSION['csrf_token'] = $token;
    // }

    // to read token from session
    public static function getSessionToken() {
        self::startSession();
        return $_SESSION['csrf_token'] ?? null;
    }
    // call from middleware
    public static function validate() {

        self::startSession();
    
        $sessionToken = self::getSessionToken();
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    
        /* Session token missing */
        if (!$sessionToken) {
            Response::error("CSRF session token missing", 403);
        }
    
        /* Header token missing */
        if (!$headerToken) {
            Response::error("CSRF token missing in request header", 403);
        }
    
        /* Token mismatch (Invalid token) */
        if (!hash_equals($sessionToken, $headerToken)) {
            Response::error("Invalid CSRF token", 403);
        }
    }
    
}

