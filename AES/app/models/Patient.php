<?php
require_once __DIR__ . '/../core/Database.php';

class Patient {

    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    /* ================= GET ALL ================= */

    public function getAll($user_id) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM patients WHERE user_id=?"
        );
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* ================= CREATE ================= */

    public function create(
        $name,
        $age,
        $gender,
        $phone,
        $address,
        $userId
    ) {

        $stmt = $this->conn->prepare("
            INSERT INTO patients
            (name, age, gender, phone, address, user_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sisssi",
            $name,
            $age,
            $gender,
            $phone,
            $address,
            $userId
        );

        return $stmt->execute();
    }

    /* ================= UPDATE ================= */

    public function update(
        $id,
        $userId,
        $name,
        $age,
        $gender,
        $phone,
        $address
    ) {

        // Ownership check
        $check = $this->conn->prepare(
            "SELECT id FROM patients WHERE id=? AND user_id=?"
        );
        $check->bind_param("ii", $id, $userId);
        $check->execute();

        if ($check->get_result()->num_rows === 0) {
            return "not_allowed";
        }

        $stmt = $this->conn->prepare("
            UPDATE patients
            SET name=?, age=?, gender=?, phone=?, address=?
            WHERE id=? AND user_id=?
        ");

        $stmt->bind_param(
            "sisssii",
            $name,
            $age,
            $gender,
            $phone,
            $address,
            $id,
            $userId
        );

        return $stmt->execute();
    }

    /* ================= DELETE ================= */

    public function delete($id, $userId) {

        $stmt = $this->conn->prepare(
            "DELETE FROM patients WHERE id=? AND user_id=?"
        );

        $stmt->bind_param("ii", $id, $userId);

        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    /* ================= GET BY ID ================= */

    public function getById($id, $userId) {

        $stmt = $this->conn->prepare(
            "SELECT * FROM patients WHERE id=? AND user_id=?"
        );

        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}
