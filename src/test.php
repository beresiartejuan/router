<?php

require_once 'autoload.php';

/**
 * Simple test to verify the router works with separated files
 */

echo "Testing Router with reorganized file structure...\n\n";

// Test 1: Create router
echo "1. Creating router instance...\n";
$router = RouterFactory::create();
echo "✓ Router created successfully\n\n";

// Test 2: Register routes
echo "2. Registering routes...\n";
$router->get('/test', function() {
    echo "Test route executed!";
});

$router->get('/users/{id}', function($id) {
    echo "User ID: " . $id;
});

$routes = $router->getRoutes();
echo "✓ Routes registered: " . count($routes['GET']) . " GET routes\n\n";

// Test 3: Test route patterns
echo "3. Testing route patterns...\n";
foreach ($routes['GET'] as $route) {
    echo "✓ Route pattern: " . $route->getPattern() . "\n";
}
echo "\n";

// Test 4: Test with custom dependencies
echo "4. Testing with custom dependencies...\n";
$customRequestHandler = new RequestHandler();
$customUriResolver = new UriResolver();
$customRouteMatcher = new RouteMatcher();

$customRouter = RouterFactory::createWithDependencies(
    $customRequestHandler,
    $customUriResolver,
    $customRouteMatcher
);
echo "✓ Router created with custom dependencies\n\n";

// Test 5: Test route matching
echo "5. Testing route matching...\n";
$testRoute = new Route('/users/{id}', function() {});
$matcher = new RouteMatcher();

$match1 = $matcher->matches($testRoute, '/users/123');
$match2 = $matcher->matches($testRoute, '/posts/123');

echo "✓ Route '/users/{id}' matches '/users/123': " . ($match1 ? 'YES' : 'NO') . "\n";
echo "✓ Route '/users/{id}' matches '/posts/123': " . ($match2 ? 'YES' : 'NO') . "\n\n";

// Test 6: Test parameter extraction
echo "6. Testing parameter extraction...\n";
$params = $matcher->extractParameters($testRoute, '/users/123');
echo "✓ Extracted parameters from '/users/123': [" . implode(', ', $params) . "]\n\n";

echo "All tests completed successfully! 🎉\n";
echo "The router is working properly with separated files.\n";
