<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/libs/RouteMatcher.php';

class RouteMatcherTest extends TestCase
{
    private $matcher;

    protected function setUp(): void
    {
        $this->matcher = new RouteMatcher();
    }

    public function testCanCreateRouteMatcher()
    {
        $this->assertInstanceOf(RouteMatcher::class, $this->matcher);
    }

    public function testSimpleRouteMatching()
    {
        $route = new Route('/test', function() {});
        
        $this->assertTrue($this->matcher->matches($route, '/test'));
        $this->assertFalse($this->matcher->matches($route, '/other'));
    }

    public function testRootRouteMatching()
    {
        $route = new Route('/', function() {});
        
        $this->assertTrue($this->matcher->matches($route, '/'));
        $this->assertFalse($this->matcher->matches($route, '/test'));
    }

    public function testParameterizedRouteMatching()
    {
        $route = new Route('/user/{id}', function() {});
        
        $this->assertTrue($this->matcher->matches($route, '/user/123'));
        $this->assertTrue($this->matcher->matches($route, '/user/abc'));
        $this->assertFalse($this->matcher->matches($route, '/user'));
        $this->assertFalse($this->matcher->matches($route, '/user/123/edit'));
    }

    public function testMultipleParametersRouteMatching()
    {
        $route = new Route('/user/{id}/post/{postId}', function() {});
        
        $this->assertTrue($this->matcher->matches($route, '/user/123/post/456'));
        $this->assertFalse($this->matcher->matches($route, '/user/123/post'));
        $this->assertFalse($this->matcher->matches($route, '/user/123'));
    }

    public function testExtractSimpleParameters()
    {
        $route = new Route('/user/{id}', function() {});
        $params = $this->matcher->extractParameters($route, '/user/123');
        
        $this->assertCount(1, $params);
        $this->assertEquals('123', $params[0]);
    }

    public function testExtractMultipleParameters()
    {
        $route = new Route('/user/{id}/post/{postId}', function() {});
        $params = $this->matcher->extractParameters($route, '/user/123/post/456');
        
        $this->assertCount(2, $params);
        $this->assertEquals('123', $params[0]);
        $this->assertEquals('456', $params[1]);
    }

    public function testExtractParametersFromNonMatchingRoute()
    {
        $route = new Route('/user/{id}', function() {});
        $params = $this->matcher->extractParameters($route, '/admin/123');
        
        $this->assertEmpty($params);
    }

    public function testComplexRoutePattern()
    {
        $route = new Route('/api/v1/user/{id}/profile', function() {});
        
        $this->assertTrue($this->matcher->matches($route, '/api/v1/user/123/profile'));
        $this->assertFalse($this->matcher->matches($route, '/api/v1/user/profile'));
        $this->assertFalse($this->matcher->matches($route, '/api/v2/user/123/profile'));
    }

    public function testCasesensitivity()
    {
        $route = new Route('/Test', function() {});
        
        $this->assertTrue($this->matcher->matches($route, '/Test'));
        $this->assertFalse($this->matcher->matches($route, '/test'));
    }
}
