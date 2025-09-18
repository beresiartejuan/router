<?php

/**
 * Router - Punto de entrada principal
 * 
 * Este archivo demuestra cómo usar el router refactorizado.
 * La clase Router es la principal y todas las dependencias están organizadas.
 */

require_once 'src/autoload.php';

// Crear instancia del router (clase principal)
$router = RouterFactory::create();

// Rutas de ejemplo
$router->get('/', function() {
    echo "<h1>Welcome to the Refactored Router!</h1>";
    echo "<p>Router class is the main entry point.</p>";
    echo "<ul>";
    echo "<li><a href='/hello/world'>Hello World Example</a></li>";
    echo "<li><a href='/users/123'>User Example</a></li>";
    echo "<li><a href='/api/status'>API Example</a></li>";
    echo "</ul>";
});

$router->get('/hello/{name}', function($name) {
    echo "<h2>Hello, " . htmlspecialchars($name) . "!</h2>";
    echo "<p>This demonstrates parameter routing.</p>";
    echo "<a href='/'>← Back to home</a>";
});

$router->get('/users/{id}', function($id) {
    echo "<h2>User Profile</h2>";
    echo "<p>User ID: " . htmlspecialchars($id) . "</p>";
    echo "<a href='/'>← Back to home</a>";
});

// API routes
$router->mount('/api', function() use ($router) {
    $router->get('/status', function() {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'OK',
            'router' => 'Refactored Router v2.0',
            'structure' => 'Organized in folders',
            'main_class' => 'Router'
        ]);
    });
});

// 404 handler
$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>The requested page could not be found.</p>";
    echo "<a href='/'>← Back to home</a>";
});

// Ejecutar el router (clase principal)
$router->run();

// Información adicional
if (php_sapi_name() === 'cli') {
    echo "\n\n=== Router Structure ===\n";
    echo "Main class: Router (src/Router.php)\n";
    echo "Interfaces: src/interfaces/\n";
    echo "Libraries: src/libs/\n";
    echo "Helpers: src/helpers/\n";
    echo "Examples: examples/\n";
    echo "\nTo see examples:\n";
    echo "php examples/basic.php\n";
    echo "php examples/advanced.php\n";
}
