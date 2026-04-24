<?php

require_once __DIR__ . '/../Repository/UserRepository.php';
require_once __DIR__ . '/../Entity/User.php';

class AuthService
{
    public function __construct(private UserRepository $users) {}

    public function register(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        string $confirm
    ): User {
        $firstName = trim($firstName);
        $lastName  = trim($lastName);
        $email     = trim($email);

        if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
            throw new RuntimeException('Wypełnij wszystkie pola.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Podaj prawidłowy adres e-mail.');
        }
        if (strlen($password) < 8) {
            throw new RuntimeException('Hasło musi mieć co najmniej 8 znaków.');
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            throw new RuntimeException('Hasło musi zawierać co najmniej jedną literę i jedną cyfrę.');
        }
        if ($password !== $confirm) {
            throw new RuntimeException('Hasła nie są identyczne.');
        }
        if ($this->users->findByEmail($email) !== null) {
            throw new RuntimeException('Ten adres e-mail jest już zajęty.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        return $this->users->create($firstName . ' ' . $lastName, $email, $hash);
    }

    public function login(string $email, string $password): User
    {
        $email = trim($email);

        if ($email === '' || $password === '') {
            throw new RuntimeException('Wypełnij wszystkie pola.');
        }

        $user = $this->users->findByEmail($email);

        if ($user === null || !password_verify($password, $user->getPassword())) {
            throw new RuntimeException('Nieprawidłowy e-mail lub hasło.');
        }

        if (!$user->isActive()) {
            throw new RuntimeException('Konto jest nieaktywne. Skontaktuj się z administratorem.');
        }

        return $user;
    }

    public function logout(): void
    {
        Session::destroy();
    }
}
