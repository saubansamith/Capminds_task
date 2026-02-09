<?php
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../helpers/Response.php';

class PatientController {

    public static function index() {
        $patientModel = new Patient();
        $patients = $patientModel->getAll();

        Response::success("Patient list", $patients);
    }

    public static function store() {
        $data = $GLOBALS['request_body'];

        if (!isset($data['name'], $data['age'], $data['gender'])) {
            Response::error("Missing required fields");
        }

        $patientModel = new Patient();
        $patientModel->create(
            $data['name'],
            $data['age'],
            $data['gender'],
            $data['phone'] ?? '',
            $data['address'] ?? ''
        );

        Response::success("Patient created successfully");
    }
}
