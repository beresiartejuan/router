<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Router.php';

class RouterPerformanceTest extends TestCase
{
    private $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function testLargeNumberOfRoutes()
    {
        $startTime = microtime(true);
        
        // Register 1000 routes
        for ($i = 0; $i < 1000; $i++) {
            $this->router->get("/route_{$i}", function() use ($i) {
                return "Route {$i}";
            });
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $routes = $this->router->getRoutes();
        $this->assertCount(1000, $routes['GET']);
        
        // Should complete within reasonable time (1 second)
        $this->assertLessThan(1.0, $executionTime, 'Route registration took too long');
    }

    public function testLargeNumberOfMiddleware()
    {
        $startTime = microtime(true);
        
        // Register 100 middleware
        for ($i = 0; $i < 100; $i++) {
            $this->router->before('GET', "/pattern_{$i}/*", function() use ($i) {
                return "Middleware {$i}";
            });
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $middleware = $this->router->getMiddleware();
        $this->assertCount(100, $middleware['GET']);
        
        // Should complete within reasonable time (0.5 seconds)
        $this->assertLessThan(0.5, $executionTime, 'Middleware registration took too long');
    }

    public function testComplexRouteMounting()
    {
        $startTime = microtime(true);
        
        $self = $this;
        
        // Create nested mounted routes
        for ($i = 0; $i < 10; $i++) {
            $this->router->mount("/api/v{$i}", function() use ($self, $i) {
                for ($j = 0; $j < 10; $j++) {
                    $self->router->mount("/module_{$j}", function() use ($self, $i, $j) {
                        for ($k = 0; $k < 5; $k++) {
                            $self->router->get("/endpoint_{$k}", function() use ($i, $j, $k) {
                                return "API v{$i} Module {$j} Endpoint {$k}";
                            });
                        }
                    });
                }
            });
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $routes = $this->router->getRoutes();
        $this->assertCount(500, $routes['GET']); // 10 * 10 * 5 = 500 routes
        
        // Should complete within reasonable time (2 seconds)
        $this->assertLessThan(2.0, $executionTime, 'Complex route mounting took too long');
    }

    public function testMemoryUsage()
    {
        $initialMemory = memory_get_usage();
        
        // Register many routes
        for ($i = 0; $i < 1000; $i++) {
            $this->router->get("/test/route/number/{$i}", function() use ($i) {
                return "Response for route {$i}";
            });
        }
        
        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;
        
        // Memory usage should be reasonable (less than 10MB for 1000 routes)
        $this->assertLessThan(10 * 1024 * 1024, $memoryUsed, 'Router uses too much memory');
        
        $routes = $this->router->getRoutes();
        $this->assertCount(1000, $routes['GET']);
    }
}
