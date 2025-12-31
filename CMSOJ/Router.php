<?php

namespace CMSOJ;

class Router
{
    protected array $routes = [];

    public function get(string $pattern, $callback, $middleware = null): void
    {
        $this->addRoute('GET', $pattern, $callback, $middleware);
    }

    public function post(string $pattern, $callback, $middleware = null): void
    {
        $this->addRoute('POST', $pattern, $callback, $middleware);
    }

    private function addRoute(string $method, string $pattern, $callback, $middleware = null): void
    {
        $pattern = trim($pattern, '/');

        $this->routes[$method][$pattern] = [
            'callback' => $callback,
            'middleware' => $middleware,
        ];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        foreach ($this->routes[$method] ?? [] as $pattern => $route) {

            // Convert {id} → regex
            $regex = preg_replace('/\{([^\/]+)\}/', '([^/]+)', $pattern);

            if (preg_match("#^$regex$#", $uri, $matches)) {

                array_shift($matches);

                // ---------------------------------
                // RUN MIDDLEWARE
                // ---------------------------------
                $middlewareClass = $route['middleware'] ?? null;

                if ($middlewareClass) {
                    if (!class_exists($middlewareClass)) {
                        throw new \Exception("Middleware class '$middlewareClass' does not exist.");
                    }
                    (new $middlewareClass())->handle();
                }


                // ---------------------------------
                // CONTROLLER: [Class::class, 'method']
                // ---------------------------------

                $callback = $route['callback'];

                if (is_array($callback) && count($callback) === 2) {

                    [$class, $method] = $callback;

                    if (is_string($class)) {
                        $class = new $class();
                    }

                    return call_user_func_array([$class, $method], $matches);
                }

                // ---------------------------------
                // CLOSURE ROUTE
                // ---------------------------------
                return call_user_func_array($callback, $matches);
            }
        }

        // 404
        http_response_code(404);
        \CMSOJ\Template::view('CMSOJ/Views/404.html');
        exit;
    }
}
