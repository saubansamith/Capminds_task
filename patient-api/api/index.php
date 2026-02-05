<?php
require_once __DIR__ . '/middleware/JsonMiddleware.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/PatientController.php';
require_once __DIR__ . '/helpers/Response.php';


$request = $_GET['request'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

$parts = explode('/', trim($request, '/'));
$controller = new PatientController($conn);

if ($parts[0] === '') {
    Response::json(200, "Patient API is running");
}

if ($parts[0] === 'patients') {
    $id = $parts[1] ?? null;

    switch ($method) {
        case 'GET':
            $id ? $controller->show($id) : $controller->index();
            break;
        case 'POST':
            $controller->store();
            break;
        case 'PUT':
            $controller->update($id);
            break;
        case 'DELETE':
            $controller->destroy($id);
            break;
        case 'PATCH':
            $controller->patch($id);
            break;
        default:
            Response::json(405, "Method Not Allowed");
    }
} else {
    Response::json(404, "Invalid Endpoint");
}
