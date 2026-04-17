<?php

class ErrorHandler {

    private static array $errors = [
        400 => [
            'title' => 'Nieprawidłowe żądanie',
            'desc'  => 'Serwer nie mógł przetworzyć żądania z powodu błędnej składni.',
        ],
        401 => [
            'title' => 'Wymagane logowanie',
            'desc'  => 'Musisz być zalogowany, aby uzyskać dostęp do tej strony.',
        ],
        403 => [
            'title' => 'Brak dostępu',
            'desc'  => 'Nie masz uprawnień do wyświetlenia tej strony.',
        ],
        404 => [
            'title' => 'Strona nie istnieje',
            'desc'  => 'Szukana strona została usunięta, przeniesiona lub nigdy nie istniała.',
        ],
        500 => [
            'title' => 'Błąd serwera',
            'desc'  => 'Coś poszło nie tak po naszej stronie. Spróbuj ponownie za chwilę.',
        ],
    ];

    public static function render(int $code): void
    {
        http_response_code($code);

        $info = self::$errors[$code] ?? [
            'title' => 'Nieoczekiwany błąd',
            'desc'  => 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie.',
        ];

        $errorCode  = $code;
        $errorTitle = $info['title'];
        $errorDesc  = $info['desc'];

        include 'src/Views/error.php';
        exit;
    }
}
