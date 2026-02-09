<?php
class JsonMiddleware {

    public static function handle() {
        header('Content-Type: application/json');

        $method = $_SERVER['REQUEST_METHOD'];

        // Only check JSON for POST, PUT, PATCH
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {

            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            if (strpos($contentType, 'application/json') === false) {
                Response::error("Content-Type must be application/json", 415);
            }

            $input = file_get_contents("php://input");
            $data = json_decode($input, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Response::error("Invalid JSON payload", 400);
            }

            $GLOBALS['request_body'] = $data;
        }
    }
}
