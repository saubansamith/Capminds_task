<?php
class Database {
    private static $conn = null;

    public static function connect() {
        if (self::$conn === null) {
            $host = $_ENV['DB_HOST'];
            $db   = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];

            self::$conn = new mysqli($host, $user, $pass, $db);

            if (self::$conn->connect_error) {
                die("Database Connection Failed: " . self::$conn->connect_error);
            }
        }

        return self::$conn;
    }
}
