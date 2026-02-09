<?php
require_once __DIR__ . '/../core/Database.php';

class Patient {

    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    public function getAll() {
        $result = $this->conn->query("SELECT * FROM patients");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function create($name, $age, $gender, $phone, $address) {
        $stmt = $this->conn->prepare(
            "INSERT INTO patients (name, age, gender, phone, address)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sisss", $name, $age, $gender, $phone, $address);
        return $stmt->execute();
    }

    public function update($id, $name, $age, $gender, $phone, $address) {
        $stmt = $this->conn->prepare(
            "UPDATE patients SET name=?, age=?, gender=?, phone=?, address=? WHERE id=?"
        );
        $stmt->bind_param("sisssi", $name, $age, $gender, $phone, $address, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM patients WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
