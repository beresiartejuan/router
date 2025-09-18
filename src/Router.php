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
    private $routes = [];
    private $beforeMiddleware = [];
    private $notFoundCallback;
    private $baseRoute = '';
    private $namespace = '';
    private $requestHandler;
    private $uriResolver;
    private $routeMatcher;

    public function __construct(
        RequestHandlerInterface $requestHandler = null,
        UriResolverInterface $uriResolver = null,
        RouteMatcherInterface $routeMatcher = null
    ) {
        $this->requestHandler = $requestHandler ?: new RequestHandler();
        $this->uriResolver = $uriResolver ?: new UriResolver();
        $this->routeMatcher = $routeMatcher ?: new RouteMatcher();
    }

    /**
     * Create a new Router instance with default dependencies
     * 
     * @return Router
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Add middleware to be executed before routes
     */
    public function before($methods, $pattern, $callback)
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
    public function match($methods, $pattern, $callback)
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
    public function get($pattern, $callback)
    {
        return $this->match('GET', $pattern, $callback);
    }

    /**
     * Register a POST route
     */
    public function post($pattern, $callback)
    {
        return $this->match('POST', $pattern, $callback);
    }

    /**
     * Register a PUT route
     */
    public function put($pattern, $callback)
    {
        return $this->match('PUT', $pattern, $callback);
    }

    /**
     * Register a DELETE route
     */
    public function delete($pattern, $callback)
    {
        return $this->match('DELETE', $pattern, $callback);
    }

    /**
     * Register a PATCH route
     */
    public function patch($pattern, $callback)
    {
        return $this->match('PATCH', $pattern, $callback);
    }

    /**
     * Register an OPTIONS route
     */
    public function options($pattern, $callback)
    {
        return $this->match('OPTIONS', $pattern, $callback);
    }

    /**
     * Register a route for all HTTP methods
     */
    public function all($pattern, $callback)
    {
        return $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $callback);
    }

    /**
     * Mount a group of routes with a base path
     */
    public function mount($baseRoute, callable $callback)
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
    public function setNamespace($namespace)
    {
        if (is_string($namespace)) {
            $this->namespace = $namespace;
        }

        return $this;
    }

    /**
     * Get the current namespace
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the 404 not found callback
     */
    public function set404($callback)
    {
        $this->notFoundCallback = $callback;
        return $this;
    }

    /**
     * Execute the router
     */
    public function run(callable $finishCallback = null)
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
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get all middleware (for testing)
     */
    public function getMiddleware()
    {
        return $this->beforeMiddleware;
    }

    /**
     * Execute before middleware for given method
     */
    private function executeMiddleware($method)
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
    private function handleRoutes($method, $uri)
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
    private function invokeCallback($callback, array $params = [])
    {
        if (is_callable($callback)) {
            call_user_func_array($callback, $params);
            return;
        }

        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($controller, $method) = explode('@', $callback);

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
    private function trigger404()
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
    private function normalizePattern($pattern)
    {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        return $this->baseRoute ? rtrim($pattern, '/') : $pattern;
    }

    /**
     * Normalize HTTP methods
     */
    private function normalizeMethods($methods)
    {
        return array_map('strtoupper', explode('|', $methods));
    }
}
