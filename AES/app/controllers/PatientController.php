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

    /* ================= GET ALL PATIENTS ================= */

    public function index() {

        $user_id = $_REQUEST['user']['user_id'];
        $data = $this->patient->getAll($user_id);

        foreach ($data as &$row) {
            $row['name']    = AES::decrypt($row['name']);
            $row['phone']   = AES::decrypt($row['phone']);
            $row['gender']  = AES::decrypt($row['gender']);
            $row['address'] = AES::decrypt($row['address']);
        }

        Response::success("Patient list", $data);
    }

    /* ================= GET SINGLE PATIENT ================= */

    public function show($id) {

        $user_id = $_REQUEST['user']['user_id'];
        $data = $this->patient->getById($id, $user_id);

        if (!$data) {
            Response::error("Patient not found", 404);
        }

        $data['name']    = AES::decrypt($data['name']);
        $data['phone']   = AES::decrypt($data['phone']);
        $data['gender']  = AES::decrypt($data['gender']);
        $data['address'] = AES::decrypt($data['address']);

        Response::success("Patient details", $data);
    }

    /* ================= CREATE PATIENT ================= */

    public function store()
    {
        $body = $GLOBALS['request_body'];
        $user_id = $_REQUEST['user']['user_id'];
    
        if (!isset($body['name'], $body['age'], $body['gender'], $body['phone'], $body['address'])) {
            Response::error("All fields are required");
        }
    
        // Encrypt data before storing
        $name    = AES::encrypt(trim($body['name']));
        $gender  = AES::encrypt(trim($body['gender']));
        $address = AES::encrypt(trim($body['address']));
    
        $phoneNormalized = preg_replace('/[^0-9]/', '', $body['phone']);
        $phone = AES::encrypt($phoneNormalized);
    
        $this->patient->create(
            $name,
            (int)$body['age'],
            $gender,
            $phone,
            $address,
            $user_id
        );
    
        Response::success("Patient created successfully");
    }
    

    /* ================= UPDATE PATIENT ================= */

    public function update($id) {

        $data = $GLOBALS['request_body'];
        $user_id = $_REQUEST['user']['user_id'];

        $nameEnc    = null;
        $genderEnc  = null;
        $addressEnc = null;
        $phoneEnc   = null;
        $phoneHash  = null;
        $age        = null;

        if (isset($data['name'])) {
            $nameEnc = AES::encrypt(trim($data['name']));
        }

        if (isset($data['gender'])) {
            $genderEnc = AES::encrypt(trim($data['gender']));
        }

        if (isset($data['address'])) {
            $addressEnc = AES::encrypt(trim($data['address']));
        }

        if (isset($data['age'])) {
            $age = (int)$data['age'];
        }

        if (isset($data['phone'])) {

            $phoneNormalized = preg_replace('/[^0-9]/', '', $data['phone']);
            $phoneHash = BlindIndex::phoneHash($phoneNormalized);

            /* Duplicate Check (excluding same record) */
            if ($this->patient->findByPhoneHash($phoneHash, $user_id, $id)) {
                Response::error("Phone number already exists", 409);
            }

            $phoneEnc = AES::encrypt($phoneNormalized);
        }

        $result = $this->patient->update(
            $id,
            $user_id,
            $nameEnc,
            $age,
            $genderEnc,
            $phoneEnc,
            $phoneHash,
            $addressEnc
        );

        if ($result === "not_allowed") {
            Response::error("You cannot update this patient", 403);
        }

        Response::success("Patient updated successfully");
    }

    /* ================= DELETE PATIENT ================= */

    public function delete($id) {

        $user_id = $_REQUEST['user']['user_id'];

        $deleted = $this->patient->delete($id, $user_id);

        if (!$deleted) {
            Response::error("Not allowed or patient not found", 403);
        }

        Response::success("Patient deleted successfully");
    }
}
