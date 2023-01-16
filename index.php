<?php

require __DIR__ . "/App/bootstrap.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];
$uriParts = explode('/', $uri);

// define all valid endpoints - this will act as a simple router
$routes = [
    'register' => [
        'method' => 'POST',
        'expression' => '/register\/?$/',
        'controller' => 'Controller\UserController',
        'function' => 'register'
    ],
    'login' => [
        'method' => 'POST',
        'expression' => '/login\/?$/',
        'controller' => 'Controller\UserController',
        'function' => 'login'
    ],
    'task.index' => [
        'method' => 'GET',
        'expression' => '/task\/all\/?$/',
        'controller' => 'Controller\TaskController',
        'function' => 'index'
    ],
    'task.create' => [
        'method' => 'POST',
        'expression' => '/task\/create\/?$/',
        'controller' => 'Controller\TaskController',
        'function' => 'create'
    ],
    'seed.data' => [
        'method' => 'GET',
        'expression' => '/seed\/data\/?$/',
        'controller' => 'Controller\SeedController',
        'function' => 'seedData'
    ],
];


$routeFound = null;
foreach ($routes as $route) {
    if ($route['method'] == $requestMethod && preg_match($route['expression'], $uri)) {
        $routeFound = $route;
        break;
    }
}

if (!$routeFound) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$function = $route['function'];
$class = $route['controller'];
$controller = new $class();
$controller->$function($uriParts);