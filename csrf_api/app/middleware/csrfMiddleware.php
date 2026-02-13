<?php
require_once __DIR__ . '/../helpers/Csrf.php';

class CsrfMiddleware {

    public static function handle() {

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Skip CSRF for auth routes
        if (
            strpos($uri, '/login') !== false ||
            strpos($uri, '/register') !== false ||
            strpos($uri, '/refresh') !== false
        ) {
            return;
        }

        // Only protect state-changing requests
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            Csrf::validate();
        }
    }
}
