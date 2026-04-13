<?php

require_once 'AppController.php';


class SecurityController extends AppController {

    public function login(): void
    {
        $this->render('auth/login', ['title' => 'Logowanie — SharePlanner']);
    }

    public function register(): void
    {
        $this->render('auth/register', ['title' => 'Rejestracja — SharePlanner']);
    }
}