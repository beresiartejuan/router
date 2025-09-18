<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Router.php';

class RouterTest extends TestCase
{
    private $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function testCanCreateRouterInstance()
    {
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testCanCreateRouterWithStaticMethod()
    {
        $router = Router::create();
        $this->assertInstanceOf(Router::class, $router);
    }

    public function testCanRegisterGetRoute()
    {
        $this->router->get('/test', function() {
            return 'test';
        });

        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('GET', $routes);
        $this->assertCount(1, $routes['GET']);
        $this->assertEquals('/test', $routes['GET'][0]->getPattern());
    }

    public function testCanRegisterPostRoute()
    {
        $this->router->post('/test', function() {
            return 'test';
        });

        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('POST', $routes);
        $this->assertCount(1, $routes['POST']);
        $this->assertEquals('/test', $routes['POST'][0]->getPattern());
    }

    public function testCanRegisterPutRoute()
    {
        $this->router->put('/test', function() {
            return 'test';
        });

        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('PUT', $routes);
        $this->assertCount(1, $routes['PUT']);
    }

    public function testCanRegisterDeleteRoute()
    {
        $this->router->delete('/test', function() {
            return 'test';
        });

        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('DELETE', $routes);
        $this->assertCount(1, $routes['DELETE']);
    }

    public function testCanRegisterPatchRoute()
    {
        $this->router->patch('/test', function() {
            return 'test';
        });

        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('PATCH', $routes);
        $this->assertCount(1, $routes['PATCH']);
    }

    public function testCanRegisterOptionsRoute()
    {
        $this->router->options('/test', function() {
            return 'test';
        });

        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('OPTIONS', $routes);
        $this->assertCount(1, $routes['OPTIONS']);
    }

    public function testCanRegisterAllMethodsRoute()
    {
        $this->router->all('/test', function() {
            return 'test';
        });

        $routes = $this->router->getRoutes();
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH', 'HEAD'];
        
        foreach ($methods as $method) {
            $this->assertArrayHasKey($method, $routes);
            $this->assertCount(1, $routes[$method]);
        }
    }

    public function testCanRegisterRouteWithMultipleMethods()
    {
        $this->router->match('GET|POST', '/test', function() {
            return 'test';
        });

        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('GET', $routes);
        $this->assertArrayHasKey('POST', $routes);
        $this->assertCount(1, $routes['GET']);
        $this->assertCount(1, $routes['POST']);
    }

    public function testCanRegisterMultipleRoutes()
    {
        $this->router->get('/test1', function() {});
        $this->router->get('/test2', function() {});
        $this->router->post('/test3', function() {});

        $routes = $this->router->getRoutes();
        $this->assertCount(2, $routes['GET']);
        $this->assertCount(1, $routes['POST']);
    }

    public function testCanRegisterMiddleware()
    {
        $this->router->before('GET', '/test/*', function() {
            return 'middleware';
        });

        $middleware = $this->router->getMiddleware();
        $this->assertArrayHasKey('GET', $middleware);
        $this->assertCount(1, $middleware['GET']);
    }

    public function testCanSetNamespace()
    {
        $this->router->setNamespace('App\\Controllers');
        $this->assertEquals('App\\Controllers', $this->router->getNamespace());
    }

    public function testCanSet404Handler()
    {
        $handler = function() {
            return '404 Not Found';
        };
        
        $result = $this->router->set404($handler);
        $this->assertSame($this->router, $result); // Test method chaining
    }

    public function testMountRoutesWithBaseRoute()
    {
        $this->router->mount('/api', function() {
            $this->router->get('/users', function() {
                return 'users';
            });
        });

        $routes = $this->router->getRoutes();
        $this->assertArrayHasKey('GET', $routes);
        $this->assertEquals('/api/users', $routes['GET'][0]->getPattern());
    }

    public function testRoutePatternNormalization()
    {
        // Test leading/trailing slashes are handled correctly
        $this->router->get('test', function() {});
        $this->router->get('/test/', function() {});
        $this->router->get('//test//', function() {});

        $routes = $this->router->getRoutes();
        
        // All should be normalized to /test
        foreach ($routes['GET'] as $route) {
            $this->assertEquals('/test', $route->getPattern());
        }
    }

    public function testMethodChaining()
    {
        $result = $this->router
            ->get('/test1', function() {})
            ->post('/test2', function() {})
            ->setNamespace('App');

        $this->assertSame($this->router, $result);
    }

    public function testCallbackTypes()
    {
        // Test closure callback
        $this->router->get('/closure', function() {
            return 'closure';
        });

        // Test string callback
        $this->router->get('/string', 'TestController@index');

        $routes = $this->router->getRoutes();
        $this->assertCount(2, $routes['GET']);
        
        $this->assertInstanceOf('Closure', $routes['GET'][0]->getCallback());
        $this->assertEquals('TestController@index', $routes['GET'][1]->getCallback());
    }
}
