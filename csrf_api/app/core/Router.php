<?php
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class Router {

    private $routes = [];

    public function add($method, $path, $handler, $protected = false) {
        $this->routes[] = compact('method', 'path', 'handler', 'protected');
    }
    // routing logic
    public function dispatch($method, $uri) {

        foreach ($this->routes as $route) {

            $pattern = preg_replace('#\{id\}#', '(\d+)', $route['path']);
            $pattern = "#^" . $pattern . "$#";

            if ($method === $route['method'] && preg_match($pattern, $uri, $matches)) {

                if ($route['protected']) {
                    AuthMiddleware::handle();
                }

                array_shift($matches); // remove full match

                $controllerName = $route['handler'][0];
                $methodName = $route['handler'][1];

                $controller = new $controllerName();

                return call_user_func_array([$controller, $methodName], $matches);

            }
        }

        Response::error("Route Not Found", 404);
    }
}
