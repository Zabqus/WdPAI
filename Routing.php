<?php

require_once 'src/controllers/AppController.php';
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/CalendarController.php';
require_once 'src/controllers/GroupsController.php';

class Routing {

    private static array $routes = [
        ''         => ['controller' => 'SecurityController',  'action' => 'login'],
        'login'    => ['controller' => 'SecurityController',  'action' => 'login'],
        'register' => ['controller' => 'SecurityController',  'action' => 'register'],
        'dashboard'=> ['controller' => 'DashboardController', 'action' => 'index'],
        'calendar' => ['controller' => 'CalendarController',  'action' => 'index'],
        'groups'   => ['controller' => 'GroupsController',   'action' => 'index'],
    ];

    public static function run(string $path): void
    {
        if (array_key_exists($path, self::$routes)) {
            $route = self::$routes[$path];
            $controller = new $route['controller'];
            $controller->{$route['action']}();
        } else {
            http_response_code(404);
            include 'src/Views/404.php';
        }
    }
}
