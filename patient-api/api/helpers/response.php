<?php
class Response {
    public static function json($status, $message, $data = []) {
        http_response_code($status);
        echo json_encode([
            "status" => $status < 400,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }
}
