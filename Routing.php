<?php

require_once 'src/controllers/AppController.php';
require_once 'src/controllers/ErrorHandler.php';
require_once 'src/controllers/Session.php';
require_once 'src/controllers/AuthController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/CalendarController.php';
require_once 'src/controllers/GroupsController.php';
require_once 'src/controllers/AdminController.php';
require_once 'src/controllers/CourseController.php';
require_once 'src/controllers/EventController.php';
require_once 'src/controllers/TaskController.php';
require_once 'src/controllers/NoteController.php';
require_once 'src/controllers/StudyPlanController.php';
require_once 'src/controllers/CsrfGuard.php';

class Routing {

    private static array $routes = [
        'GET' => [
            ''            => ['AuthController',      'loginForm'],
            'login'       => ['AuthController',      'loginForm'],
            'register'    => ['AuthController',      'registerForm'],
            'logout'      => ['AuthController',      'logout'],
            'dashboard'   => ['DashboardController', 'index'],
            'calendar'    => ['CalendarController',  'index'],
            'groups'      => ['GroupsController',    'index'],
            'admin'       => ['AdminController',     'index'],
            'courses'     => ['CourseController',    'index'],
            'api/courses' => ['CourseController',    'list'],
            'events'      => ['EventController',     'index'],
            'api/events'  => ['EventController',     'list'],
            'api/tasks'   => ['TaskController',      'list'],
            'notes'       => ['NoteController',      'index'],
            'api/notes'      => ['NoteController',      'list'],
            'study-plan'     => ['StudyPlanController', 'index'],
            'api/study-plan' => ['StudyPlanController', 'list'],
        ],
        'POST' => [
            'login'          => ['AuthController',  'login'],
            'register'       => ['AuthController',  'register'],
            'admin/role'     => ['AdminController', 'updateRole'],
            'admin/delete'   => ['AdminController', 'delete'],
            'admin/toggle'   => ['AdminController', 'toggleActive'],
            'courses/create' => ['CourseController', 'create'],
            'courses/update' => ['CourseController', 'update'],
            'courses/delete' => ['CourseController', 'delete'],
            'events/create'  => ['EventController',  'create'],
            'events/update'  => ['EventController',  'update'],
            'events/delete'  => ['EventController',  'delete'],
            'events/toggle'  => ['EventController',  'toggle'],
            'tasks/create'   => ['TaskController',   'create'],
            'tasks/toggle'   => ['TaskController',   'toggle'],
            'tasks/update'   => ['TaskController',   'update'],
            'tasks/delete'   => ['TaskController',   'delete'],
            'tasks/reorder'  => ['TaskController',   'reorder'],
            'notes/create'   => ['NoteController',   'create'],
            'notes/update'   => ['NoteController',   'update'],
            'notes/delete'        => ['NoteController',      'delete'],
            'study-plan/create'   => ['StudyPlanController', 'create'],
            'study-plan/delete'   => ['StudyPlanController', 'delete'],
        ],
    ];

    private static array $protected = [
        'dashboard', 'calendar', 'groups',
        'courses', 'api/courses',
        'courses/create', 'courses/update', 'courses/delete',
        'events', 'api/events',
        'events/create', 'events/update', 'events/delete', 'events/toggle',
        'api/tasks',
        'tasks/create', 'tasks/toggle', 'tasks/update', 'tasks/delete', 'tasks/reorder',
        'notes', 'api/notes',
        'notes/create', 'notes/update', 'notes/delete',
        'study-plan', 'api/study-plan',
        'study-plan/create', 'study-plan/delete',
    ];

    private static array $adminOnly = [
        'admin', 'admin/role', 'admin/delete', 'admin/toggle',
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
        self::roleGuard($path);

        if ($method === 'POST') {
            CsrfGuard::validate();
        }

        [$controllerName, $action] = $methodRoutes[$path];
        (new $controllerName)->$action();
    }

    private static function authGuard(string $path): void
    {
        $requiresAuth = in_array($path, self::$protected, true)
                     || in_array($path, self::$adminOnly, true);

        if (!$requiresAuth || Session::has('user_id')) {
            return;
        }

        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
            ErrorHandler::render(401);
        } else {
            header('Location: /login');
            exit;
        }
    }

    private static function roleGuard(string $path): void
    {
        if (!in_array($path, self::$adminOnly, true)) {
            return;
        }

        if (Session::get('user_role') !== 'admin') {
            ErrorHandler::render(403);
            exit;
        }
    }
}
