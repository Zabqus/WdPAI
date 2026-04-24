<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Repository/UserRepository.php';
require_once __DIR__ . '/../Services/AuthService.php';

class AuthController extends AppController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService(new UserRepository());
    }

    public function loginForm(): void
    {
        if (Session::has('user_id')) {
            $this->redirect('dashboard');
        }
        $this->render('auth/login');
    }

    public function login(): void
    {
        try {
            $user = $this->auth->login(
                $_POST['email']    ?? '',
                $_POST['password'] ?? ''
            );
            Session::regenerate();
            Session::set('user_id',   $user->getId());
            Session::set('user_name', $user->getUsername());
            Session::set('user_role', $user->getRole());

            if ($this->isAjax()) {
                $this->json(['success' => true, 'redirect' => '/dashboard']);
            } else {
                $this->redirect('dashboard');
            }
        } catch (RuntimeException $e) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            } else {
                $this->render('auth/login', ['error' => $e->getMessage()]);
            }
        }
    }

    public function registerForm(): void
    {
        if (Session::has('user_id')) {
            $this->redirect('dashboard');
        }
        $this->render('auth/register');
    }

    public function register(): void
    {
        try {
            $user = $this->auth->register(
                $_POST['first_name']       ?? '',
                $_POST['last_name']        ?? '',
                $_POST['email']            ?? '',
                $_POST['password']         ?? '',
                $_POST['password_confirm'] ?? ''
            );
            Session::regenerate();
            Session::set('user_id',   $user->getId());
            Session::set('user_name', $user->getUsername());
            Session::set('user_role', $user->getRole());
            $this->redirect('dashboard');
        } catch (RuntimeException $e) {
            $this->render('auth/register', ['error' => $e->getMessage()]);
        }
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->redirect('login');
    }
}
