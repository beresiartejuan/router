<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/libs/Route.php';

class RouteTest extends TestCase
{
    public function testCanCreateRoute()
    {
        $route = new Route('/test', function() {
            return 'test';
        });

        $this->assertInstanceOf(Route::class, $route);
    }

    public function testCanGetPattern()
    {
        $pattern = '/test/pattern';
        $route = new Route($pattern, function() {});

        $this->assertEquals($pattern, $route->getPattern());
    }

    public function testCanGetCallback()
    {
        $callback = function() {
            return 'test';
        };
        $route = new Route('/test', $callback);

        $this->assertSame($callback, $route->getCallback());
    }

    public function testCanCreateRouteWithStringCallback()
    {
        $callback = 'TestController@index';
        $route = new Route('/test', $callback);

        $this->assertEquals($callback, $route->getCallback());
    }

    public function testCanCreateRouteWithArrayCallback()
    {
        $callback = ['TestController', 'index'];
        $route = new Route('/test', $callback);

        $this->assertEquals($callback, $route->getCallback());
    }
}
