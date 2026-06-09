<?php

use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe AuthService — walidacja rejestracji.
 *
 * UserRepository jest mockowany (PHPUnit nie wywołuje jego konstruktora),
 * więc testy działają bez połączenia z bazą danych.
 */
class AuthServiceTest extends TestCase
{
    private AuthService $auth;
    /** @var \PHPUnit\Framework\MockObject\MockObject&UserRepository */
    private \PHPUnit\Framework\MockObject\MockObject $repoMock;

    protected function setUp(): void
    {
        // Wyczyść pliki rate-limiter z poprzednich przebiegów (IP CLI = 0.0.0.0)
        RateLimiter::clear('login', '0.0.0.0');

        $this->repoMock = $this->createMock(UserRepository::class);
        $this->auth     = new AuthService($this->repoMock);
    }

    // =========================================================
    //  register()
    // =========================================================

    public function testRegisterThrowsOnEmptyFirstName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Wypełnij wszystkie pola.');

        $this->auth->register('', 'Kowalski', 'jan@example.com', 'Pass1!abc', 'Pass1!abc');
    }

    public function testRegisterThrowsOnInvalidEmail(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Podaj prawidłowy adres e-mail.');

        $this->auth->register('Jan', 'Kowalski', 'to-nie-email', 'Pass1!abc', 'Pass1!abc');
    }

    public function testRegisterThrowsOnShortPassword(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Hasło musi mieć co najmniej 8 znaków.');

        // "P1!" → 3 znaki
        $this->auth->register('Jan', 'Kowalski', 'jan@example.com', 'P1!', 'P1!');
    }

    public function testRegisterThrowsOnPasswordWithoutSpecialChar(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Hasło musi zawierać co najmniej jeden znak specjalny');

        // "Password1" → 9 znaków, litery + cyfra, brak znaku specjalnego
        $this->auth->register('Jan', 'Kowalski', 'jan@example.com', 'Password1', 'Password1');
    }

    public function testRegisterThrowsOnPasswordMismatch(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Hasła nie są identyczne.');

        $this->auth->register('Jan', 'Kowalski', 'jan@example.com', 'Pass1!abc', 'Pass1!XYZ');
    }

    public function testRegisterThrowsOnDuplicateEmail(): void
    {
        // Symulujemy istniejącego użytkownika w bazie
        $existingUser = new User(1, 'Jan Kowalski', 'jan@example.com', 'hash', 'user', '2024-01-01', true);
        $this->repoMock->method('findByEmail')->willReturn($existingUser);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Ten adres e-mail jest już zajęty.');

        $this->auth->register('Jan', 'Kowalski', 'jan@example.com', 'Pass1!abc', 'Pass1!abc');
    }
}
