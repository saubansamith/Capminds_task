<?php

require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../helpers/Response.php';

class PatientController {

    private $patient;

    public function __construct() {
        $this->patient = new Patient();
    }

    public function index() {
        $user_id = $_REQUEST['user']['user_id'];  // ✅ FIX
        $data = $this->patient->getAll($user_id);
        Response::success("Patient list", $data);
    }

    public function show($id) {
        $user_id = $_REQUEST['user']['user_id'];  // ✅ FIX
        $data = $this->patient->getById($id, $user_id);

        if (!$data) {
            Response::error("Patient not found", 404);
        }

        Response::success("Patient details", $data);
    }

    public function store() {
        $body = $GLOBALS['request_body'];
        $user_id = $_REQUEST['user']['user_id'];  // ✅ FIX

        $this->patient->create(
            $body['name'],
            $body['age'],
            $body['gender'],
            $body['phone'],
            $body['address'],
            $user_id
        );

        Response::success("Patient created successfully");
    }

    public static function update($id) {
        $data = $GLOBALS['request_body'];
        $user = $_REQUEST['user']; // from JWT
    
        $patientModel = new Patient();
    
        $result = $patientModel->update(
            $id,
            $user['user_id'],
            $data['name'],
            $data['age'],
            $data['gender'],
            $data['phone'] ?? '',
            $data['address'] ?? ''
        );
    
        if ($result === "not_allowed") {
            Response::error("You cannot update this patient", 403);
        }
    
        Response::success("Patient updated successfully");
    }
    

    public function delete($id) {
        $user_id = $_REQUEST['user']['user_id'];  // ✅ FIX

        $deleted = $this->patient->delete($id, $user_id);

        if (!$deleted) {
            Response::error("Not allowed or patient not found", 403);
        }

        Response::success("Patient deleted successfully");
    }
}
