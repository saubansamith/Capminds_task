<?php
// ---------------- LOAD REQUIRED FILES ----------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$basePath = dirname(__DIR__);

require_once $basePath . '/app/helpers/Response.php';
require_once $basePath . '/app/helpers/JWT.php';
require_once $basePath . '/app/helpers/Csrf.php';
require_once $basePath . '/app/middleware/JsonMiddleware.php';
require_once $basePath . '/app/middleware/AuthMiddleware.php';
require_once $basePath . '/app/middleware/CsrfMiddleware.php';
require_once $basePath . '/app/core/Router.php';
require_once $basePath . '/app/controllers/AuthController.php';
require_once $basePath . '/app/controllers/PatientController.php';




// ---------------- LOAD .env VARIABLES ----------------
$envPath = __DIR__ . '/../.env';
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($key, $value) = explode('=', $line, 2);
    $_ENV[trim($key)] = trim($value);
}

// ---------------- RUN JSON MIDDLEWARE ----------------
JsonMiddleware::handle();
// 
CsrfMiddleware::handle();
// ---------------- INITIALIZE ROUTER ----------------
$router = new Router();

// ---------- PUBLIC ROUTES ----------
$router->add('POST', '/api/register', [AuthController::class, 'register']);
$router->add('POST', '/api/login', [AuthController::class, 'login']);
$router->add('POST', '/api/refresh', [AuthController::class, 'refresh']);

// ---------- PROTECTED ROUTES ----------
$router->add('GET', '/api/patients', [PatientController::class, 'index'], true);
$router->add('GET', '/api/patients/{id}', [PatientController::class, 'show'], true);
$router->add('POST', '/api/patients', [PatientController::class, 'store'], true);
$router->add('PUT', '/api/patients/{id}', [PatientController::class, 'update'], true);
$router->add('DELETE', '/api/patients/{id}', [PatientController::class, 'delete'], true);

// ---------------- DISPATCH REQUEST ----------------
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove project folder (WAMP fix)
$requestUri = str_replace('/Rest_api_jwt/public', '', $requestUri);

// Dispatch
$router->dispatch($requestMethod, $requestUri);
