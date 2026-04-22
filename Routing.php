<?php

require_once 'src/controllers/AppController.php';
require_once 'src/controllers/ErrorHandler.php';
require_once 'src/controllers/Session.php';
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/CalendarController.php';
require_once 'src/controllers/GroupsController.php';

class Routing {

    /**
     * Routes keyed by HTTP method then path.
     * Format: 'METHOD' => [ 'path' => ['controller', 'action'] ]
     */
    private static array $routes = [
        'GET' => [
            ''          => ['SecurityController',  'loginForm'],
            'login'     => ['SecurityController',  'loginForm'],
            'register'  => ['SecurityController',  'registerForm'],
            'logout'    => ['SecurityController',  'logout'],
            'dashboard' => ['DashboardController', 'index'],
            'calendar'  => ['CalendarController',  'index'],
            'groups'    => ['GroupsController',    'index'],
        ],
        'POST' => [
            'login'    => ['SecurityController', 'login'],
            'register' => ['SecurityController', 'register'],
        ],
    ];

    public static function run(string $path): void
    {
        Session::start();

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $methodRoutes = self::$routes[$method] ?? [];

        if (array_key_exists($path, $methodRoutes)) {
            [$controllerName, $action] = $methodRoutes[$path];
            $controller = new $controllerName;
            $controller->$action();
        } elseif ($method === 'POST' && array_key_exists($path, self::$routes['GET'])) {
            // POST to a GET-only route — method not allowed
            ErrorHandler::render(400);
        } else {
            ErrorHandler::render(404);
        }
    }
}
