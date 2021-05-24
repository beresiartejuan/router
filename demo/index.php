<?php

    // In case one is using PHP 5.4's built-in server
    $filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

    // Include the Router class
    // @note: it's recommended to just use the composer autoloader when working with other packages too
    require_once __DIR__ . '/../src/Bramus/Router/Router.php';

    // Custom 404 Handler
    Router::set404(function () {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo '404, route not found!';
    });

    // Before Router Middleware
    Router::before('GET', '/.*', function () {
        header('X-Powered-By: bramus/router');
    });

    // Static route: / (homepage)
    Router::get('/', function () {
        echo '<h1>bramus/router</h1><p>Try these routes:<p><ul><li>/hello/<em>name</em></li><li>/blog</li><li>/blog/<em>year</em></li><li>/blog/<em>year</em>/<em>month</em></li><li>/blog/<em>year</em>/<em>month</em>/<em>day</em></li><li>/movies</li><li>/movies/<em>id</em></li></ul>';
    });

    // Static route: /hello
    Router::get('/hello', function () {
        echo '<h1>bramus/router</h1><p>Visit <code>/hello/<em>name</em></code> to get your Hello World mojo on!</p>';
    });

    // Dynamic route: /hello/name
    Router::get('/hello/(\w+)', function ($name) {
        echo 'Hello ' . htmlentities($name);
    });

    // Dynamic route: /ohai/name/in/parts
    Router::get('/ohai/(.*)', function ($url) {
        echo 'Ohai ' . htmlentities($url);
    });

    // Dynamic route with (successive) optional subpatterns: /blog(/year(/month(/day(/slug))))
    Router::get('/blog(/\d{4}(/\d{2}(/\d{2}(/[a-z0-9_-]+)?)?)?)?', function ($year = null, $month = null, $day = null, $slug = null) {
        if (!$year) {
            echo 'Blog overview';

            return;
        }
        if (!$month) {
            echo 'Blog year overview (' . $year . ')';

            return;
        }
        if (!$day) {
            echo 'Blog month overview (' . $year . '-' . $month . ')';

            return;
        }
        if (!$slug) {
            echo 'Blog day overview (' . $year . '-' . $month . '-' . $day . ')';

            return;
        }
        echo 'Blogpost ' . htmlentities($slug) . ' detail (' . $year . '-' . $month . '-' . $day . ')';
    });

    // Subrouting
    Router::mount('/movies', function () {

        // will result in '/movies'
        Router::get('/', function () {
            echo 'movies overview';
        });

        // will result in '/movies'
        Router::post('/', function () {
            echo 'add movie';
        });

        // will result in '/movies/id'
        Router::get('/(\d+)', function ($id) {
            echo 'movie id ' . htmlentities($id);
        });

        // will result in '/movies/id'
        Router::put('/(\d+)', function ($id) {
            echo 'Update movie id ' . htmlentities($id);
        });
    });

    // Thunderbirds are go!
    Router::run();

// EOF
