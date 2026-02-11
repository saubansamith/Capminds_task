<?php
require_once __DIR__ . '/../core/Database.php';

class Patient {

    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function getAll($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM patients WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    

    public function create($name, $age, $gender, $phone, $address, $user_id) {
        $stmt = $this->conn->prepare(
            "INSERT INTO patients (name, age, gender, phone, address, user_id)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sisssi", $name, $age, $gender, $phone, $address, $user_id);
        return $stmt->execute();
    }
    

    public function update($id, $userId, $name, $age, $gender, $phone, $address) {

        // Step 1: Check ownership
        $check = $this->conn->prepare(
            "SELECT id FROM patients WHERE id=? AND user_id=?"
        );
        $check->bind_param("ii", $id, $userId);
        $check->execute();
        $result = $check->get_result();
    
        if ($result->num_rows === 0) {
            return "not_allowed";
        }
    
        // Step 2: Update
        $stmt = $this->conn->prepare(
            "UPDATE patients 
             SET name=?, age=?, gender=?, phone=?, address=? 
             WHERE id=?"
        );
        $stmt->bind_param("sisssi", $name, $age, $gender, $phone, $address, $id);
    
        return $stmt->execute();
    }
    

    public function delete($id, $userId) {
        $stmt = $this->conn->prepare(
            "DELETE FROM patients WHERE id=? AND user_id=?"
        );
        $stmt->bind_param("ii", $id, $userId);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
    
    public function getById($id, $userId) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM patients WHERE id=? AND user_id=?"
        );
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    
}
