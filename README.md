# Router - Librería PHP de Enrutamiento

Esta es una librería PHP de enrutamiento independiente, basada originalmente en [bramus/router](https://github.com/bramus/router) de Bram(us) Van Damme.

## Reconocimiento

Esta librería está inspirada y basada en el excelente trabajo de **Bram(us) Van Damme** en su librería [bramus/router](https://github.com/bramus/router).

## Instalación

### Via Composer

```bash
composer require beresiartejuan/router
```

## Métodos Principales

### Creación del Router

```php
// Crear una nueva instancia con dependencias por defecto
$router = Router::create();

// O crear con dependencias personalizadas (para casos avanzados)
$router = new Router($requestHandler, $uriResolver, $routeMatcher);
```

### Registro de Rutas por Método HTTP

#### Métodos HTTP Individuales

```php
// GET - para obtener datos
$router->get('/users', function() {
    // Listar usuarios
});

// POST - para crear nuevos recursos
$router->post('/users', function() {
    // Crear nuevo usuario
});

// PUT - para actualizar recursos completos
$router->put('/users/{id}', function($id) {
    // Actualizar usuario completo
});

// PATCH - para actualizaciones parciales
$router->patch('/users/{id}', function($id) {
    // Actualizar campos específicos del usuario
});

// DELETE - para eliminar recursos
$router->delete('/users/{id}', function($id) {
    // Eliminar usuario
});

// OPTIONS - para información sobre métodos permitidos
$router->options('/users', function() {
    // Retornar métodos HTTP permitidos
});
```

#### Múltiples Métodos HTTP

```php
// Registrar la misma ruta para múltiples métodos
$router->match('GET|POST', '/form', function() {
    // Manejar tanto GET como POST
});

// Registrar para todos los métodos HTTP
$router->all('/api/status', function() {
    // Responder a cualquier método HTTP
});
```

### Montaje de Rutas (Subrutas)

El montaje te permite agrupar rutas bajo un prefijo común:

```php
// Montar todas las rutas de API bajo /api
$router->mount('/api', function() use ($router) {

    // Estas rutas tendrán el prefijo /api

    // GET /api/users
    $router->get('/users', function() {
        echo json_encode(['users' => []]);
    });

    // GET /api/users/{id}
    $router->get('/users/{id}', function($id) {
        echo json_encode(['user' => ['id' => $id]]);
    });

    // POST /api/users
    $router->post('/users', function() {
        echo json_encode(['message' => 'Usuario creado']);
    });
});

// Montaje de rutas administrativas
$router->mount('/admin', function() use ($router) {

    // GET /admin/dashboard
    $router->get('/dashboard', function() {
        echo 'Panel de administración';
    });

    // Submontaje anidado: /admin/users/*
    $router->mount('/users', function() use ($router) {

        // GET /admin/users/
        $router->get('/', function() {
            echo 'Lista de usuarios admin';
        });

        // GET /admin/users/{id}/edit
        $router->get('/{id}/edit', function($id) {
            echo "Editar usuario $id";
        });
    });
});
```

### Middleware (Funciones Intermedias)

#### Before Middleware

Ejecuta código antes de que se procese la ruta:

```php
// Middleware para todas las rutas de admin
$router->before('GET|POST', '/admin/.*', function() {
    // Verificar autenticación
    if (!isset($_SESSION['admin'])) {
        header('Location: /login');
        exit();
    }
});

// Middleware para rutas específicas
$router->before('POST', '/api/.*', function() {
    // Verificar token API
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!validateApiToken($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido']);
        exit();
    }
});
```

### Manejo de Errores

#### Página 404 Personalizada

```php
$router->set404(function() {
    http_response_code(404);
    echo '<h1>Página no encontrada</h1>';
    echo '<p>La página que buscas no existe.</p>';
});

// O usando una clase controladora
$router->set404('ErrorController@notFound');
```

#### Disparar 404 Manualmente

```php
$router->get('/posts/{id}', function($id) {
    $post = getPost($id);

    if (!$post) {
        // Disparar el manejador 404
        $router->trigger404();
        return;
    }

    echo "Post: " . $post['title'];
});
```

### Ejecución del Router

```php
// Ejecutar el router (procesar la solicitud actual)
$router->run();

// Ejecutar con callback final
$router->run(function() {
    // Este código se ejecuta después de procesar la ruta
    echo "
<!-- Procesado por mi router -->";
});
```

### Configuración Adicional

#### Establecer Namespace Base

```php
$router->setNamespace('Appontrollers');

// Ahora puedes usar nombres cortos de clases
$router->get('/users', 'UserController@index');
// Equivale a: 'AppontrollersserController@index'
```

#### Establecer Ruta Base

```php
// Útil si tu aplicación está en un subdirectorio
$router->setBasePath('/mi-app');
```

## Uso Básico

```php
require_once 'vendor/autoload.php';

use Router\Router;

// Crear router
$router = Router::create();

// Registrar rutas
$router->get('/users/{id}', function($id) {
    echo "Usuario: $id";
});

$router->post('/users', 'UserController@create');

// Ejecutar
$router->run();
```

## Estructura de la Librería

```
src/
├── Router.php                      # 🎯 Clase principal del router
├── interfaces/                     # 🔌 Interfaces del sistema
│   ├── RequestHandlerInterface.php
│   ├── UriResolverInterface.php
│   └── RouteMatcherInterface.php
├── libs/                          # 📚 Clases principales del sistema
│   ├── Route.php                  # Representación de una ruta
│   ├── Request.php                # Representación de una petición HTTP
│   ├── RequestHandler.php         # Manejo de peticiones HTTP
│   ├── UriResolver.php           # Resolución de URIs
│   └── RouteMatcher.php          # Matching de patrones de ruta
└── helpers/                       # 🛠️ Clases auxiliares
    └── RouterFactory.php          # Factory para crear instancias
```

## Características Principales

### ✅ **Arquitectura Modular**

- Separación clara de responsabilidades
- Interfaces bien definidas
- Inyección de dependencias

### ✅ **Fácil Testing**

- Dependencias inyectables
- Mocks disponibles
- Métodos públicos para inspección

### ✅ **Flexible y Extensible**

- Intercambio fácil de componentes
- Configuración personalizable
- Soporte para diferentes entornos

### ✅ **PSR Compatible**

- Autoloading PSR-4
- Namespaces consistentes
- Estándares modernos de PHP

## Ejemplo Avanzado

```php
use Router\Router;
use Router\Libs\RequestHandler;
use Router\Libs\UriResolver;
use Router\Libs\RouteMatcher;

// Crear router con dependencias personalizadas
$requestHandler = new RequestHandler();
$uriResolver = new UriResolver();
$routeMatcher = new RouteMatcher();

$router = new Router($requestHandler, $uriResolver, $routeMatcher);

// Registrar rutas con parámetros
$router->get('/api/users/{id}/posts/{postId}', function($id, $postId) {
    return json_encode([
        'user_id' => $id,
        'post_id' => $postId
    ]);
});

// Rutas con métodos HTTP específicos
$router->post('/api/users', 'UserController@store');
$router->put('/api/users/{id}', 'UserController@update');
$router->delete('/api/users/{id}', 'UserController@destroy');

$router->run();
```

## Route Patterns

Los patrones de ruta definen qué URLs coincidirán con cada ruta. Pueden ser estáticos o dinámicos:

### Patrones Estáticos

Los patrones estáticos coinciden exactamente con la URL solicitada:

```php
$router->get('/about', function() {
    echo 'Página Acerca de';
});

$router->get('/contact', function() {
    echo 'Página de Contacto';
});

$router->get('/blog/latest', function() {
    echo 'Últimas entradas del blog';
});
```

### Patrones Dinámicos con Placeholders

Usa placeholders entre llaves `{}` para capturar segmentos variables de la URL:

```php
// Capturar un ID de usuario
$router->get('/users/{id}', function($id) {
    echo "Usuario ID: " . $id;
});

// Capturar múltiples parámetros
$router->get('/users/{userId}/posts/{postId}', function($userId, $postId) {
    echo "Usuario: $userId, Post: $postId";
});

// Parámetros opcionales con valores por defecto
$router->get('/blog/{year?}', function($year = null) {
    if ($year) {
        echo "Posts del año: " . $year;
    } else {
        echo "Todos los posts";
    }
});
```

### Patrones Dinámicos con Expresiones Regulares (PCRE)

Para mayor control, puedes usar expresiones regulares:

```php
// Solo números (uno o más dígitos)
$router->get('/users/(\d+)', function($id) {
    echo "Usuario ID: " . $id;
});

// Solo letras y números
$router->get('/profile/(\w+)', function($username) {
    echo "Perfil de: " . $username;
});

// Patrón más específico: exactamente 4 dígitos para el año
$router->get('/blog/(\d{4})', function($year) {
    echo "Posts del año: " . $year;
});

// Múltiples patrones
$router->get('/blog/(\d{4})/(\d{2})', function($year, $month) {
    echo "Posts de $month/$year";
});
```

### Patrones Opcionales

Puedes hacer partes de la ruta opcionales usando `?`:

```php
// Blog con parámetros opcionales sucesivos
$router->get('/blog(/(\d{4})(/(\d{2})(/(\d{2}))?)?)?', function($year = null, $month = null, $day = null) {
    if (!$year) {
        echo 'Vista general del blog';
        return;
    }
    if (!$month) {
        echo "Posts del año $year";
        return;
    }
    if (!$day) {
        echo "Posts de $month/$year";
        return;
    }
    echo "Posts del $day/$month/$year";
});
```

### Comodines

Para capturar cualquier cosa:

```php
// Capturar todo lo que viene después
$router->get('/files/(.*)', function($path) {
    echo "Ruta del archivo: " . $path;
});

// Capturar segmento que no contenga barras
$router->get('/category/([^/]+)', function($category) {
    echo "Categoría: " . $category;
});
```

## Ejemplos Prácticos

### Aplicación REST API Básica

```php
<?php
require_once 'vendor/autoload.php';

use Router\Router;

$router = Router::create();

// Configurar para API JSON
header('Content-Type: application/json');

// Middleware para validar API key
$router->before('GET|POST|PUT|DELETE', '/api/.*', function() {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if ($apiKey !== 'mi-api-key-secreta') {
        http_response_code(401);
        echo json_encode(['error' => 'API key requerida']);
        exit();
    }
});

// Montar rutas de API
$router->mount('/api/v1', function() use ($router) {

    // Usuarios
    $router->get('/users', function() {
        echo json_encode(['users' => getUserList()]);
    });

    $router->get('/users/{id}', function($id) {
        $user = getUser($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }
        echo json_encode(['user' => $user]);
    });

    $router->post('/users', function() {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = createUser($data);
        http_response_code(201);
        echo json_encode(['user' => $user]);
    });

    $router->put('/users/{id}', function($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = updateUser($id, $data);
        echo json_encode(['user' => $user]);
    });

    $router->delete('/users/{id}', function($id) {
        deleteUser($id);
        http_response_code(204);
    });
});

$router->run();
```

### Uso con Clases Controladoras

```php
<?php
require_once 'vendor/autoload.php';

use Router\Router;

$router = Router::create();

// Establecer namespace para controladores
$router->setNamespace('App\\Controllers');

// Rutas usando controladores
$router->get('/', 'HomeController@index');
$router->get('/about', 'PageController@about');

// Rutas de usuario
$router->mount('/users', function() use ($router) {
    $router->get('/', 'UserController@index');
    $router->get('/{id}', 'UserController@show');
    $router->post('/', 'UserController@store');
    $router->put('/{id}', 'UserController@update');
    $router->delete('/{id}', 'UserController@destroy');
});

// Área administrativa con middleware
$router->before('GET|POST|PUT|DELETE', '/admin/.*', 'AuthController@requireAdmin');

$router->mount('/admin', function() use ($router) {
    $router->get('/dashboard', 'Admin\\DashboardController@index');
    $router->get('/users', 'Admin\\UserController@index');
    $router->get('/settings', 'Admin\\SettingsController@index');
});

$router->run();
```

## Características Avanzadas

### Soporte para Subfolders

El router automáticamente detecta si está ejecutándose en un subfolder y ajusta las rutas:

```php
// Si tu app está en: https://example.com/mi-app/
// El router automáticamente prefijará todas las rutas con /mi-app

$router->get('/', function() {
    // Responderá a: https://example.com/mi-app/
});

$router->get('/users', function() {
    // Responderá a: https://example.com/mi-app/users
});
```

### Override del Método HTTP

Soporta `X-HTTP-Method-Override` para simular métodos PUT, DELETE, etc. desde formularios HTML:

```html
<!-- Formulario HTML -->
<form method="POST" action="/users/123">
  <input type="hidden" name="_method" value="DELETE" />
  <!-- Será tratado como DELETE /users/123 -->
</form>
```

### Parámetros de Consulta y POST Data

```php
$router->post('/users', function() {
    // Datos POST
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';

    // Parámetros de consulta
    $sort = $_GET['sort'] ?? 'name';

    // JSON data
    $json = json_decode(file_get_contents('php://input'), true);

    // Crear usuario...
});
```

## Licencia

MIT License - ver archivo LICENSE para más detalles.

## Créditos

- **Librería original**: [bramus/router](https://github.com/bramus/router) por Bram(us) Van Damme
- **Esta implementación**: Desarrollada independientemente basándose en los conceptos originales
