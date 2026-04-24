<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Repository/UserRepository.php';

class SecurityController extends AppController {

    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
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
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->render('auth/login', ['error' => 'Wypełnij wszystkie pola.']);
            return;
        }

        $user = $this->users->findByEmail($email);

        if ($user === null || !password_verify($password, $user->getPassword())) {
            $this->render('auth/login', ['error' => 'Nieprawidłowy e-mail lub hasło.']);
            return;
        }

        if (!$user->isActive()) {
            $this->render('auth/login', ['error' => 'Konto jest nieaktywne. Skontaktuj się z administratorem.']);
            return;
        }

        Session::regenerate();
        Session::set('user_id',   $user->getId());
        Session::set('user_name', $user->getUsername());
        Session::set('user_role', $user->getRole());

        $this->redirect('dashboard');
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
        $firstName = trim($_POST['first_name']       ?? '');
        $lastName  = trim($_POST['last_name']        ?? '');
        $email     = trim($_POST['email']            ?? '');
        $password  = $_POST['password']              ?? '';
        $confirm   = $_POST['password_confirm']      ?? '';

        $error = $this->validateRegistration($firstName, $lastName, $email, $password, $confirm);

        if ($error !== null) {
            $this->render('auth/register', ['error' => $error]);
            return;
        }

        if ($this->users->findByEmail($email) !== null) {
            $this->render('auth/register', ['error' => 'Ten adres e-mail jest już zajęty.']);
            return;
        }

        $username = $firstName . ' ' . $lastName;
        $hash     = password_hash($password, PASSWORD_BCRYPT);

        $user = $this->users->create($username, $email, $hash);

        Session::regenerate();
        Session::set('user_id',   $user->getId());
        Session::set('user_name', $user->getUsername());
        Session::set('user_role', $user->getRole());

        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('login');
    }

    private function validateRegistration(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        string $confirm
    ): ?string {
        if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
            return 'Wypełnij wszystkie pola.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Podaj prawidłowy adres e-mail.';
        }
        if (strlen($password) < 8) {
            return 'Hasło musi mieć co najmniej 8 znaków.';
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return 'Hasło musi zawierać co najmniej jedną literę i jedną cyfrę.';
        }
        if ($password !== $confirm) {
            return 'Hasła nie są identyczne.';
        }
        return null;
    }
}
