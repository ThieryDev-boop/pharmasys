<?php
// app/core/Router.php
class Router {
    private array $routes = [];

    public function add(string $method, string $path, string $controller, string $action): void {
        $this->routes[] = compact('method', 'path', 'controller', 'action');
    }

    public function dispatch(string $method, string $uri): void {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                $controllerFile = APP_PATH . '/controllers/' . $route['controller'] . '.php';
                if (!file_exists($controllerFile)) {
                    http_response_code(500);
                    die('Contrôleur introuvable.');
                }
                require_once $controllerFile;
                $controller = new $route['controller']();
                $action = $route['action'];
                $controller->$action();
                return;
            }
        }
        http_response_code(404);
        require_once APP_PATH . '/views/errors/404.php';
    }
}
