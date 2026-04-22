<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    public function loginForm(): void
    {
        $this->render('auth/login');
    }

    public function login(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->render('auth/login', ['error' => 'Wypełnij wszystkie pola.']);
            return;
        }

        // TODO: verify credentials against DB
        // Placeholder — accept any non-empty input for now
        Session::regenerate();
        Session::set('user_id',    0);
        Session::set('user_email', $email);
        Session::set('user_name',  explode('@', $email)[0]);
        Session::set('user_role',  'user');

        $this->redirect('dashboard');
    }

    public function registerForm(): void
    {
        $this->render('auth/register');
    }

    public function register(): void
    {
        // TODO: implement registration logic
        $this->redirect('login');
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('login');
    }
}
