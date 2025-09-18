<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Router.php';

class RouterIntegrationTest extends TestCase
{
    private $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        
        // Mock $_SERVER variables for testing
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    }

    protected function tearDown(): void
    {
        // Clean up output buffers after each test
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    public function testBasicRouteRegistrationAndMatching()
    {
        $this->router->get('/', function() {
            echo 'home';
        });

        $this->router->get('/about', function() {
            echo 'about';
        });

        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('GET', $routes);
        $this->assertCount(2, $routes['GET']);
    }

    public function testMiddlewareRegistration()
    {
        $middlewareExecuted = false;
        
        $this->router->before('GET', '/admin/*', function() use (&$middlewareExecuted) {
            $middlewareExecuted = true;
        });

        $this->router->get('/admin/dashboard', function() {
            echo 'dashboard';
        });

        $middleware = $this->router->getMiddleware();
        $this->assertArrayHasKey('GET', $middleware);
        $this->assertCount(1, $middleware['GET']);
    }

    public function testNamespaceSettingAndRetrieval()
    {
        $namespace = 'App\\Controllers';
        $this->router->setNamespace($namespace);
        
        $this->assertEquals($namespace, $this->router->getNamespace());
    }

    public function testMethodChaining()
    {
        $result = $this->router
            ->get('/test1', function() {})
            ->post('/test2', function() {})
            ->put('/test3', function() {})
            ->delete('/test4', function() {})
            ->patch('/test5', function() {})
            ->options('/test6', function() {})
            ->setNamespace('App')
            ->set404(function() {});

        $this->assertInstanceOf(Router::class, $result);
    }

    public function testMountingWithNestedRoutes()
    {
        $self = $this;
        
        $this->router->mount('/api', function() use ($self) {
            $self->router->get('/users', function() {
                echo 'users';
            });
            
            $self->router->mount('/v1', function() use ($self) {
                $self->router->get('/posts', function() {
                    echo 'posts';
                });
            });
        });

        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('GET', $routes);
        
        $patterns = array_map(function($route) {
            return $route->getPattern();
        }, $routes['GET']);
        
        $this->assertContains('/api/users', $patterns);
        $this->assertContains('/api/v1/posts', $patterns);
    }

    public function testAllMethodRegistration()
    {
        $this->router->all('/universal', function() {
            echo 'universal';
        });

        $routes = $this->router->getRoutes();
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH', 'HEAD'];
        
        foreach ($methods as $method) {
            $this->assertArrayHasKey($method, $routes);
            $this->assertCount(1, $routes[$method]);
            $this->assertEquals('/universal', $routes[$method][0]->getPattern());
        }
    }

    public function testMultipleMiddlewareOnSameRoute()
    {
        $this->router->before('GET|POST', '/protected/*', function() {
            echo 'auth middleware';
        });

        $this->router->before('GET', '/protected/*', function() {
            echo 'logging middleware';
        });

        $middleware = $this->router->getMiddleware();
        $this->assertCount(2, $middleware['GET']); // GET has both auth and logging middleware
        $this->assertCount(1, $middleware['POST']); // POST only has auth middleware
    }

    public function testRoutePatternNormalization()
    {
        // Test various pattern formats
        $this->router->get('test', function() {});           // no leading slash
        $this->router->get('/test/', function() {});        // trailing slash
        $this->router->get('//test//', function() {});      // multiple slashes

        $routes = $this->router->getRoutes();
        
        foreach ($routes['GET'] as $route) {
            $this->assertEquals('/test', $route->getPattern());
        }
    }

    public function testCallbackTypes()
    {
        // Test closure
        $closure = function() { return 'closure'; };
        $this->router->get('/closure', $closure);

        // Test string controller reference
        $this->router->get('/controller', 'TestController@index');

        // Test array callback
        $this->router->get('/array', ['TestController', 'index']);

        $routes = $this->router->getRoutes();
        $this->assertCount(3, $routes['GET']);
        
        $this->assertInstanceOf('Closure', $routes['GET'][0]->getCallback());
        $this->assertEquals('TestController@index', $routes['GET'][1]->getCallback());
        $this->assertEquals(['TestController', 'index'], $routes['GET'][2]->getCallback());
    }

    public function testEmptyRoutePattern()
    {
        $this->router->get('', function() {
            echo 'root';
        });

        $routes = $this->router->getRoutes();
        $this->assertEquals('/', $routes['GET'][0]->getPattern());
    }
}
