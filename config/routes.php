<?php
/**
 * Router simple y propio
 * Soporta GET y POST con parámetros dinámicos (/evento/{slug})
 * Aplica middleware en grupo por prefijo
 */

class Router
{
    private array $routes     = [];
    private array $middleware = [];

    /**
     * Registra una ruta GET
     */
    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Registra una ruta POST
     */
    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Agrupa rutas bajo un prefijo con middleware compartido
     */
    public function group(string $prefix, array $middleware, callable $callback): void
    {
        // Guardar el estado actual del prefijo y middleware
        $previousMiddleware = $this->middleware;
        $this->middleware   = array_merge($this->middleware, $middleware);

        // Registrar rutas dentro del grupo con el prefijo
        $groupRouter = new GroupRouter($prefix, $this);
        $callback($groupRouter);

        $this->middleware = $previousMiddleware;
    }

    /**
     * Agrega una ruta al registro interno
     */
    public function addRoute(string $method, string $path, array $handler, array $extraMiddleware = []): void
    {
        // Convertir parámetros {param} a regex capturadora
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $path);
        $pattern = '@^' . $pattern . '$@';

        // Extraer nombres de parámetros en el orden que aparecen
        preg_match_all('/\{([a-zA-Z_]+)\}/', $path, $paramNames);

        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'pattern'    => $pattern,
            'paramNames' => $paramNames[1],
            'handler'    => $handler,
            'middleware' => array_merge($this->middleware, $extraMiddleware),
        ];
    }

    /**
     * Despacha la petición HTTP actual
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $_GET['url'] ?? '/';
        $uri    = '/' . trim($uri, '/');

        // Remover query string si quedó pegado al URI
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            // Extraer parámetros de la URL
            array_shift($matches);
            $params = array_combine($route['paramNames'], $matches) ?: [];

            // Ejecutar middleware en orden
            foreach ($route['middleware'] as $middlewareClass) {
                $mw = new $middlewareClass();
                $mw->handle($params);
            }

            // Instanciar el controlador y llamar al método
            [$controllerClass, $method] = $route['handler'];
            $controller = new $controllerClass();
            $controller->$method($params);
            return;
        }

        // No se encontró ninguna ruta — 404
        $this->notFound();
    }

    /**
     * Respuesta 404 personalizada
     */
    private function notFound(): void
    {
        http_response_code(404);
        // Intentar cargar la vista 404 si existe
        $view404 = VIEWS_PATH . '/errors/404.php';
        if (file_exists($view404)) {
            include $view404;
        } else {
            echo '<h1>404 — Página no encontrada</h1>';
            echo '<p><a href="' . APP_URL . '">Volver al inicio</a></p>';
        }
    }
}

/**
 * Router proxy que agrega el prefijo del grupo a cada ruta registrada
 */
class GroupRouter
{
    public function __construct(
        private string $prefix,
        private Router $router
    ) {}

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->router->addRoute('GET', $this->prefix . $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->router->addRoute('POST', $this->prefix . $path, $handler, $middleware);
    }

    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $this->router->group($this->prefix . $prefix, $middleware, $callback);
    }
}
