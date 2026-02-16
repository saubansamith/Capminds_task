<?php

require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AES.php';
require_once __DIR__ . '/../helpers/BlindIndex.php';


class PatientController {

    private $patient;

    public function __construct() {
        $this->patient = new Patient();
    }
    // it show all the details of the particular user 
    public function index() {

        $user_id = $_REQUEST['user']['user_id'];
        $data = $this->patient->getAll($user_id);
    
        foreach ($data as &$row) {
            $row['phone'] = AES::decrypt($row['phone_encrypted']);
        }
    
        Response::success("Patient list", $data);
    }
    
    // to get one patient
    public function show($id) {

        $user_id = $_REQUEST['user']['user_id'];
        $data = $this->patient->getById($id, $user_id);
    
        if (!$data) {
            Response::error("Patient not found", 404);
        }
    
        /* ---------- DECRYPT PHONE ---------- */
    
        $data['phone'] = AES::decrypt($data['phone_encrypted']);
    
        Response::success("Patient details", $data);
    }
    
    // create a new patient
    public function store() {

        $body = $GLOBALS['request_body'];
        $user_id = $_REQUEST['user']['user_id'];
    
        /* ---------- NORMALIZE ---------- */
    
        $phoneNormalized = preg_replace('/[^0-9]/', '', $body['phone']);
    
        /* ---------- AES ENCRYPT ---------- */
    
        $phoneEncrypted = AES::encrypt($phoneNormalized);
    
        /* ---------- BLIND INDEX HASH ---------- */
    
        $phoneHash = BlindIndex::phoneHash($phoneNormalized);
    
        /* ---------- DUPLICATE CHECK ---------- */
    
        $existing = $this->patient->findByPhoneHash($phoneHash, $user_id);
    
        if ($existing) {
            Response::error("Phone already exists", 409);
        }
    
        /* ---------- SAVE ---------- */
    
        $this->patient->create(
            $body['name'],
            $body['age'],
            $body['gender'],
            $phoneEncrypted,
            $phoneHash,
            $body['address'],
            $user_id
        );
    
        Response::success("Patient created successfully");
    }
    
    
    // /updating the particular details using id
    public static function update($id) {

        $data = $GLOBALS['request_body'];
        $user = $_REQUEST['user'];
    
        $patientModel = new Patient();
    
        /* Encrypt Phone if exists */
    
        $phoneEncrypted = '';
        $phoneHash = '';
    
        if (!empty($data['phone'])) {
    
            $phoneNormalized = trim($data['phone']);
    
            $phoneEncrypted = AES::encrypt($phoneNormalized);
            $phoneHash = BlindIndex::hash($phoneNormalized);
        }
    
        $result = $patientModel->update(
            $id,
            $user['user_id'],
            $data['name'],
            $data['age'],
            $data['gender'],
            $phoneEncrypted,
            $phoneHash,
            $data['address'] ?? ''
        );
    
        if ($result === "not_allowed") {
            Response::error("You cannot update this patient", 403);
        }
    
        Response::success("Patient updated successfully");
    }
    
    

    public function delete($id) {
        $user_id = $_REQUEST['user']['user_id'];  

        $deleted = $this->patient->delete($id, $user_id);

        if (!$deleted) {
            Response::error("Not allowed or patient not found", 403);
        }

        Response::success("Patient deleted successfully");
    }
}
