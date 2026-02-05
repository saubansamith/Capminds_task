<?php
class Patient {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllPatients() {
        $result = $this->conn->query("SELECT * FROM patients");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPatientById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM patients WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createPatient($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO patients (name, age, gender, phone) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("siss", $data['name'], $data['age'], $data['gender'], $data['phone']);
        return $stmt->execute();
    }

    public function updatePatient($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE patients SET name=?, age=?, gender=?, phone=? WHERE id=?"
        );
        $stmt->bind_param("sissi", $data['name'], $data['age'], $data['gender'], $data['phone'], $id);
        return $stmt->execute();
    }

    public function deletePatient($id) {
        $stmt = $this->conn->prepare("DELETE FROM patients WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    public function patchPatient($id, $data) {
        $fields = [];
        $values = [];
        $types  = "";
    
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
    
            // detect type
            $types .= is_int($value) ? "i" : "s";
        }
    
        $sql = "UPDATE patients SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "i";
        $values[] = $id;
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
    
        return $stmt->execute();
    }
    
}
