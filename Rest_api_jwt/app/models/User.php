<?php
require_once __DIR__ . '/../core/Database.php';

class User {

    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($name, $email, $password) {
        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $name, $email, $password);
        return $stmt->execute();
    }
}
