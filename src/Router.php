<?php

require_once __DIR__ . '/libs/Route.php';
require_once __DIR__ . '/libs/Request.php';
require_once __DIR__ . '/interfaces/RequestHandlerInterface.php';
require_once __DIR__ . '/libs/RequestHandler.php';
require_once __DIR__ . '/interfaces/UriResolverInterface.php';
require_once __DIR__ . '/libs/UriResolver.php';
require_once __DIR__ . '/interfaces/RouteMatcherInterface.php';
require_once __DIR__ . '/libs/RouteMatcher.php';

/**
 * Refactored Router - More testable version
 * @author      Refactored for better testability
 * @license     MIT public license
 */
class Router
{
    private array $routes = [];
    private array $beforeMiddleware = [];
    private mixed $notFoundCallback = null;
    private string $baseRoute = '';
    private string $namespace = '';
    private RequestHandlerInterface $requestHandler;
    private UriResolverInterface $uriResolver;
    private RouteMatcherInterface $routeMatcher;

    public function __construct(
        ?RequestHandlerInterface $requestHandler = null,
        ?UriResolverInterface $uriResolver = null,
        ?RouteMatcherInterface $routeMatcher = null
    ) {
        $this->requestHandler = $requestHandler ?? new RequestHandler();
        $this->uriResolver = $uriResolver ?? new UriResolver();
        $this->routeMatcher = $routeMatcher ?? new RouteMatcher();
    }

    /**
     * Create a new Router instance with default dependencies
     */
    public static function create(): Router
    {
        return new self();
    }

    /**
     * Add middleware to be executed before routes
     */
    public function before(string|array $methods, string $pattern, mixed $callback): static
    {
        $pattern = $this->normalizePattern($pattern);
        $methods = $this->normalizeMethods($methods);

        foreach ($methods as $method) {
            $this->beforeMiddleware[$method][] = new Route($pattern, $callback);
        }

        return $this;
    }

    /**
     * Register a route
     */
    public function match(string|array $methods, string $pattern, mixed $callback): static
    {
        $pattern = $this->normalizePattern($pattern);
        $methods = $this->normalizeMethods($methods);

        foreach ($methods as $method) {
            $this->routes[$method][] = new Route($pattern, $callback);
        }

        return $this;
    }

    /**
     * Register a GET route
     */
    public function get(string $pattern, mixed $callback): static
    {
        return $this->match('GET', $pattern, $callback);
    }

    /**
     * Register a POST route
     */
    public function post(string $pattern, mixed $callback): static
    {
        return $this->match('POST', $pattern, $callback);
    }

    /**
     * Register a PUT route
     */
    public function put(string $pattern, mixed $callback): static
    {
        return $this->match('PUT', $pattern, $callback);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $pattern, mixed $callback): static
    {
        return $this->match('DELETE', $pattern, $callback);
    }

    /**
     * Register a PATCH route
     */
    public function patch(string $pattern, mixed $callback): static
    {
        return $this->match('PATCH', $pattern, $callback);
    }

    /**
     * Register an OPTIONS route
     */
    public function options(string $pattern, mixed $callback): static
    {
        return $this->match('OPTIONS', $pattern, $callback);
    }

    /**
     * Register a route for all HTTP methods
     */
    public function all(string $pattern, mixed $callback): static
    {
        return $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $callback);
    }

    /**
     * Mount a group of routes with a base path
     */
    public function mount(string $baseRoute, callable $callback): static
    {
        $originalBaseRoute = $this->baseRoute;
        $this->baseRoute .= $baseRoute;

        call_user_func($callback);

        $this->baseRoute = $originalBaseRoute;

        return $this;
    }

    /**
     * Set the namespace for controller resolution
     */
    public function setNamespace(string $namespace): static
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Get the current namespace
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Set the 404 not found callback
     */
    public function set404(mixed $callback): static
    {
        $this->notFoundCallback = $callback;
        return $this;
    }

    /**
     * Execute the router
     */
    public function run(?callable $finishCallback = null): bool
    {
        $request = $this->requestHandler->getCurrentRequest();
        $uri = $this->uriResolver->getCurrentUri();

        // Execute before middleware
        $this->executeMiddleware($request->getMethod());

        // Handle routes
        $handled = $this->handleRoutes($request->getMethod(), $uri);

        if (!$handled) {
            $this->trigger404();
        } elseif ($finishCallback && is_callable($finishCallback)) {
            call_user_func($finishCallback);
        }

        // Clean up HEAD requests
        if ($request->getOriginalMethod() === 'HEAD') {
            ob_end_clean();
        }

        return $handled;
    }

    /**
     * Get all registered routes (for testing)
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get all middleware (for testing)
     */
    public function getMiddleware(): array
    {
        return $this->beforeMiddleware;
    }

    /**
     * Execute before middleware for given method
     */
    private function executeMiddleware(string $method): void
    {
        if (!isset($this->beforeMiddleware[$method])) {
            return;
        }

        $uri = $this->uriResolver->getCurrentUri();

        foreach ($this->beforeMiddleware[$method] as $middleware) {
            if ($this->routeMatcher->matches($middleware, $uri)) {
                $params = $this->routeMatcher->extractParameters($middleware, $uri);
                $this->invokeCallback($middleware->getCallback(), $params);
            }
        }
    }

    /**
     * Handle routes for given method and URI
     */
    private function handleRoutes(string $method, string $uri): bool
    {
        if (!isset($this->routes[$method])) {
            return false;
        }

        foreach ($this->routes[$method] as $route) {
            if ($this->routeMatcher->matches($route, $uri)) {
                $params = $this->routeMatcher->extractParameters($route, $uri);
                $this->invokeCallback($route->getCallback(), $params);
                return true;
            }
        }

        return false;
    }

    /**
     * Invoke a callback with parameters
     */
    private function invokeCallback(mixed $callback, array $params = []): void
    {
        if (is_callable($callback)) {
            call_user_func_array($callback, $params);
            return;
        }

        if (is_string($callback) && strpos($callback, '@') !== false) {
            [$controller, $method] = explode('@', $callback, 2);

            if ($this->namespace) {
                $controller = $this->namespace . '\\' . $controller;
            }

            if (class_exists($controller) && method_exists($controller, $method)) {
                $reflection = new ReflectionMethod($controller, $method);

                if ($reflection->isStatic()) {
                    forward_static_call_array([$controller, $method], $params);
                } else {
                    $instance = new $controller();
                    call_user_func_array([$instance, $method], $params);
                }
            }
        }
    }

    /**
     * Trigger 404 not found
     */
    private function trigger404(): void
    {
        if ($this->notFoundCallback) {
            $this->invokeCallback($this->notFoundCallback);
        } else {
            $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
            header($protocol . ' 404 Not Found');
        }
    }

    /**
     * Normalize route pattern
     */
    private function normalizePattern(string $pattern): string
    {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        return $this->baseRoute ? rtrim($pattern, '/') : $pattern;
    }

    /**
     * Normalize HTTP methods
     */
    private function normalizeMethods(string|array $methods): array
    {
        if (is_array($methods)) {
            return array_map('strtoupper', $methods);
        }
        return array_map('strtoupper', explode('|', $methods));
    }
}
