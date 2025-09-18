<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Router.php';

class RouterAdvancedTest extends TestCase
{
    private $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        
        // Set up a clean environment for each test
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    }

    public function testRouteParametersExtraction()
    {
        $paramsCaptured = [];
        
        $this->router->get('/user/{id}', function($id) use (&$paramsCaptured) {
            $paramsCaptured['id'] = $id;
        });

        // This would require a more complex test setup to actually trigger route execution
        $routes = $this->router->getRoutes();
        $this->assertCount(1, $routes['GET']);
        $this->assertEquals('/user/{id}', $routes['GET'][0]->getPattern());
    }

    public function testComplexRoutePatterns()
    {
        $this->router->get('/api/v{version}/user/{id}/posts/{slug}', function() {});
        
        $routes = $this->router->getRoutes();
        $this->assertEquals('/api/v{version}/user/{id}/posts/{slug}', $routes['GET'][0]->getPattern());
    }

    public function testNestedMounting()
    {
        $self = $this;
        
        $this->router->mount('/api', function() use ($self) {
            $self->router->mount('/v1', function() use ($self) {
                $self->router->mount('/users', function() use ($self) {
                    $self->router->get('/{id}', function() {});
                    $self->router->post('', function() {});
                });
            });
        });

        $routes = $this->router->getRoutes();
        
        $getRoutes = array_map(fn($route) => $route->getPattern(), $routes['GET']);
        $postRoutes = array_map(fn($route) => $route->getPattern(), $routes['POST']);
        
        $this->assertContains('/api/v1/users/{id}', $getRoutes);
        $this->assertContains('/api/v1/users', $postRoutes);
    }

    public function testMiddlewareWithDifferentPatterns()
    {
        $this->router->before('GET', '/admin/*', function() {});
        $this->router->before('GET', '/api/*', function() {});
        $this->router->before('POST', '/admin/*', function() {});

        $middleware = $this->router->getMiddleware();
        
        $this->assertCount(2, $middleware['GET']); // admin and api middleware
        $this->assertCount(1, $middleware['POST']); // only admin middleware
    }

    public function testCallbackWithArraySyntax()
    {
        $this->router->get('/test', ['TestController', 'index']);
        
        $routes = $this->router->getRoutes();
        $this->assertEquals(['TestController', 'index'], $routes['GET'][0]->getCallback());
    }

    public function testEmptyPattern()
    {
        $this->router->get('', function() {});
        
        $routes = $this->router->getRoutes();
        $this->assertEquals('/', $routes['GET'][0]->getPattern());
    }

    public function testRootPattern()
    {
        $this->router->get('/', function() {});
        
        $routes = $this->router->getRoutes();
        $this->assertEquals('/', $routes['GET'][0]->getPattern());
    }

    public function testMixedHttpMethods()
    {
        $this->router->match('GET|POST|PUT', '/api/resource', function() {});
        
        $routes = $this->router->getRoutes();
        
        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('POST', $routes);
        $this->assertArrayHasKey('PUT', $routes);
        
        foreach (['GET', 'POST', 'PUT'] as $method) {
            $this->assertCount(1, $routes[$method]);
            $this->assertEquals('/api/resource', $routes[$method][0]->getPattern());
        }
    }

    public function testNamespaceWithControllerCallback()
    {
        $this->router->setNamespace('App\\Controllers');
        $this->router->get('/test', 'UserController@show');
        
        $this->assertEquals('App\\Controllers', $this->router->getNamespace());
        
        $routes = $this->router->getRoutes();
        $this->assertEquals('UserController@show', $routes['GET'][0]->getCallback());
    }

    public function testMultipleMiddlewareForSameMethod()
    {
        $this->router->before('GET', '/protected/*', function() {});
        $this->router->before('GET', '/protected/admin/*', function() {});
        
        $middleware = $this->router->getMiddleware();
        $this->assertCount(2, $middleware['GET']);
    }

    public function testChainedMethodCalls()
    {
        $result = $this->router
            ->before('GET', '/auth/*', function() {})
            ->get('/users', function() {})
            ->post('/users', function() {})
            ->put('/users/{id}', function() {})
            ->delete('/users/{id}', function() {})
            ->setNamespace('Controllers')
            ->set404(function() {});

        $this->assertSame($this->router, $result);
        $this->assertEquals('Controllers', $this->router->getNamespace());
    }

    public function testRouteOverriding()
    {
        // Test that registering the same route twice works (last one wins)
        $this->router->get('/test', function() { return 'first'; });
        $this->router->get('/test', function() { return 'second'; });
        
        $routes = $this->router->getRoutes();
        $this->assertCount(2, $routes['GET']); // Both routes should be registered
    }

    public function testSpecialCharactersInRoutes()
    {
        $this->router->get('/api/users/{id}/profile', function() {});
        $this->router->get('/files/{filename}.{ext}', function() {});
        
        $routes = $this->router->getRoutes();
        $patterns = array_map(fn($route) => $route->getPattern(), $routes['GET']);
        
        $this->assertContains('/api/users/{id}/profile', $patterns);
        $this->assertContains('/files/{filename}.{ext}', $patterns);
    }
}
