<?php
// Load required files manually (simple autoload)
require_once __DIR__ . '/../app/helpers/Response.php';
require_once __DIR__ . '/../app/helpers/JWT.php';
require_once __DIR__ . '/../app/middleware/JsonMiddleware.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/PatientController.php';

// ---------------- LOAD .env ----------------
$envPath = __DIR__ . '/../.env';
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($key, $value) = explode('=', $line, 2);
    $_ENV[trim($key)] = trim($value);
}

// ---------------- RUN JSON MIDDLEWARE ----------------
JsonMiddleware::handle();

// ---------------- INITIALIZE ROUTER ----------------
$router = new Router();

// Public Routes
$router->add('POST', '/api/register', [AuthController::class, 'register']);
$router->add('POST', '/api/login', [AuthController::class, 'login']);

// Protected Routes (JWT required)
$router->add('GET', '/api/patients', [PatientController::class, 'index'], true);
$router->add('POST', '/api/patients', [PatientController::class, 'store'], true);

// ---------------- DISPATCH REQUEST ----------------
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove project folder name if needed
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = str_replace('/Rest_api_jwt/public', '', $requestUri);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestMethod, $requestUri);
