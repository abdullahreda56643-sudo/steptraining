<?php

declare(strict_types=1);

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $basePath = parse_url(APP_URL, PHP_URL_PATH) ?: '/';
        $basePath = rtrim($basePath, '/');
        if ($basePath !== '' && ($path === $basePath || strpos($path, $basePath . '/') === 0)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }
        $path = '/' . trim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (isset($this->routes[$method][$path])) {
            $this->callHandler($this->routes[$method][$path]);
            return;
        }

        foreach ($this->routes[$method] as $route => $handler) {
            if (strpos($route, '{') === false) {
                continue;
            }

            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $route);
            $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';

            if (preg_match($pattern, $path, $matches)) {
                $this->callHandler($handler, ...array_slice($matches, 1));
                return;
            }
        }

        http_response_code(404);
        echo view('errors/404', ['message' => 'الصفحة المطلوبة غير موجودة.'], 'main');
    }

    private function callHandler(callable|array $handler, ...$parameters): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $instance = new $class();
            $instance->$method(...$parameters);
            return;
        }

        $handler(...$parameters);
    }
}
