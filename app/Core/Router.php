<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $pattern, array|callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array|callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, array|callable $handler): void
    {
        // Convert :param segments to named capture groups
        $regex = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $pattern);
        $this->routes[] = compact('method', 'regex', 'handler');
    }

    public function dispatch(Request $request): void
    {
        $uri    = rtrim($request->uri, '/') ?: '/';
        $method = $request->method;

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $regex = '#^' . $route['regex'] . '$#';
            if (preg_match($regex, $uri, $matches)) {
                // Named params only
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->invoke($route['handler'], $params, $request);
                return;
            }
        }

        // 404
        http_response_code(404);
        $view = new View();
        $view->render('errors/404', ['title' => 'Page Not Found']);
    }

    private function invoke(array|callable $handler, array $params, Request $request): void
    {
        if (is_callable($handler)) {
            call_user_func($handler, $request, $params);
            return;
        }
        [$class, $method] = $handler;
        $controller = new $class($request);
        $controller->$method($params);
    }
}
