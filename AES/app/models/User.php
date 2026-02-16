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

    public function create($name, $emailEncrypted, $emailHash, $password) {

        $stmt = $this->conn->prepare("
            INSERT INTO users (name, email_encrypted, email_hash, password)
            VALUES (?, ?, ?, ?)
        ");
    
        $stmt->bind_param("ssss", $name, $emailEncrypted, $emailHash, $password);
        return $stmt->execute();
    }
    
    public function findByEmailHash($emailHash) {

        $stmt = $this->conn->prepare("
            SELECT * FROM users WHERE email_hash = ?
        ");
    
        $stmt->bind_param("s", $emailHash);
        $stmt->execute();
    
        return $stmt->get_result()->fetch_assoc();
    }
    
}
