<?php

class CsrfGuard
{
    private const KEY = '_csrf';

    public static function token(): string
    {
        if (!Session::has(self::KEY)) {
            Session::set(self::KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::KEY);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    public static function validate(): void
    {
        $submitted = $_POST['_csrf'] ?? '';
        $stored    = Session::get(self::KEY, '');

        if ($stored === '' || !hash_equals($stored, $submitted)) {
            ErrorHandler::render(403);
            exit;
        }
    }
}
