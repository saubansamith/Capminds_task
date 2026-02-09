<?php
class Router {
    private $routes = [];

    public function add($method, $path, $handler, $protected = false) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'protected' => $protected
        ];
    }

    public function dispatch($requestMethod, $requestUri) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $requestUri) {
                
                // If route is protected, run AuthMiddleware
                if ($route['protected']) {
                    AuthMiddleware::handle();
                }

                call_user_func($route['handler']);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(["message" => "Route Not Found"]);
    }
}
