<?php
// app/core/Router.php
class Router {
    private array $routes = [];

    public function add(string $method, string $pattern, string $controllerAction): void {
        // Convert pattern to regex (remove trailing slash)
        $pattern = trim($pattern, '/');
        $regex = '#^' . $pattern . '$#';
        $this->routes[] = [$method, $regex, $controllerAction];
    }

    public function dispatch(string $requestMethod, string $url): void {
        $url = trim($url, '/');
        foreach ($this->routes as $route) {
            [$method, $regex, $controllerAction] = $route;
            if (strcasecmp($requestMethod, $method) !== 0) {
                continue;
            }
            if (preg_match($regex, $url, $matches)) {
                // Remove full match
                array_shift($matches);
                $parts = explode('@', $controllerAction);
                $controllerName = $parts[0];
                $action = $parts[1];
                $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
                if (!file_exists($controllerFile)) {
                    http_response_code(500);
                    echo "Controller file not found: $controllerFile";
                    return;
                }
                require_once $controllerFile;
                $controller = new $controllerName();
                if (!method_exists($controller, $action)) {
                    http_response_code(500);
                    echo "Action $action not found in $controllerName";
                    return;
                }
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }
        // No matching route
        http_response_code(404);
        echo "Page not found";
    }
}
?>
