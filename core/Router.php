<?php

namespace Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_GET['url'] ?? '';
        $url = trim($url, '/');

        // Try exact match first
        if (isset($this->routes[$method][$url])) {
            $this->callAction($this->routes[$method][$url]);
            return;
        }

        // Try parameterized routes
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
            if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->callAction($handler, $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        if (file_exists(BASE_PATH . '/app/Views/errors/404.php')) {
            require BASE_PATH . '/app/Views/errors/404.php';
        } else {
            echo '404 Not Found';
        }
    }

    private function callAction(string $handler, array $params = []): void
    {
        [$controllerName, $method] = explode('@', $handler);
        $controllerClass = "App\\Controllers\\{$controllerName}";
        $controllerFile = BASE_PATH . "/app/Controllers/{$controllerName}.php";

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            echo "Controller not found: {$controllerName}";
            return;
        }

        require_once $controllerFile;
        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            http_response_code(500);
            echo "Method not found: {$controllerName}@{$method}";
            return;
        }

        call_user_func_array([$controller, $method], $params);
    }
}
