<?php

require_once 'src/controllers/AppController.php';
require_once 'src/controllers/ErrorHandler.php';
require_once 'src/controllers/Session.php';
require_once 'src/controllers/AuthController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/CalendarController.php';
require_once 'src/controllers/GroupsController.php';

class Routing {

    private static array $routes = [
        'GET' => [
            ''          => ['AuthController',      'loginForm'],
            'login'     => ['AuthController',      'loginForm'],
            'register'  => ['AuthController',      'registerForm'],
            'logout'    => ['AuthController',      'logout'],
            'dashboard' => ['DashboardController', 'index'],
            'calendar'  => ['CalendarController',  'index'],
            'groups'    => ['GroupsController',    'index'],
        ],
        'POST' => [
            'login'    => ['AuthController', 'login'],
            'register' => ['AuthController', 'register'],
        ],
    ];

    private static array $protected = [
        'dashboard', 'calendar', 'groups',
    ];

    public static function run(string $path): void
    {
        Session::start();

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $methodRoutes = self::$routes[$method] ?? [];

        if (!array_key_exists($path, $methodRoutes)) {
            if ($method === 'POST' && array_key_exists($path, self::$routes['GET'])) {
                ErrorHandler::render(400);
            } else {
                ErrorHandler::render(404);
            }
            return;
        }

        self::authGuard($path);

        [$controllerName, $action] = $methodRoutes[$path];
        (new $controllerName)->$action();
    }

    private static function authGuard(string $path): void
    {
        if (!in_array($path, self::$protected, true)) {
            return;
        }

        if (!Session::has('user_id')) {
            header('Location: /login');
            exit;
        }
    }
}
