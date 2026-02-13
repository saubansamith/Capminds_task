<?php

class Csrf {

    public static function generate() {
        return bin2hex(random_bytes(32));
    }

    public static function storeInSession($token) {
        $_SESSION['csrf_token'] = $token;
    }

    public static function getSessionToken() {
        return $_SESSION['csrf_token'] ?? null;
    }

    public static function validate() {

        $sessionToken = self::getSessionToken();
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$sessionToken || !$headerToken || !hash_equals($sessionToken, $headerToken)) {
            Response::error("CSRF validation failed", 403);
        }
    }
}

