<?php
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../models/Patient.php';

class PatientController {
    private $model;

    public function __construct($db) {
        $this->model = new Patient($db);
    }

    public function index() {
        $data = $this->model->getAllPatients();
        Response::json(200, "All patients", $data);
    }

    public function show($id) {
        $data = $this->model->getPatientById($id);
        if ($data) {
            Response::json(200, "Patient found", $data);
        } else {
            Response::json(404, "Patient not found");
        }
    }

    public function store() {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            Response::json(400, "Invalid input");
        }

        $this->model->createPatient($input);
        Response::json(201, "Patient created");
    }

    public function update($id) {
        $input = json_decode(file_get_contents("php://input"), true);
        $this->model->updatePatient($id, $input);
        Response::json(200, "Patient updated");
    }

    public function destroy($id) {
        $this->model->deletePatient($id);
        Response::json(200, "Patient deleted");
    }
    public function patch($id) {
        $input = json_decode(file_get_contents("php://input"), true);
    
        if (!$input) {
            Response::json(400, "Invalid input");
        }
    
        $patient = $this->model->getPatientById($id);
        if (!$patient) {
            Response::json(404, "Patient not found");
        }
    
        $this->model->patchPatient($id, $input);
        Response::json(200, "Patient partially updated");
    }
    
    
}
