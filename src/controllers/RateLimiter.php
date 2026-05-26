<?php

class RateLimiter
{
    private const MAX_ATTEMPTS    = 5;
    private const WINDOW_SECONDS  = 900; // 15-minute sliding window
    private const LOCKOUT_SECONDS = 900; // 15-minute lockout after exceeding limit

    public static function check(string $action, string $identifier): void
    {
        $file = self::filePath($action, $identifier);
        $data = self::load($file);

        if (time() < $data['locked_until']) {
            $wait = $data['locked_until'] - time();
            throw new RuntimeException(
                "Zbyt wiele nieudanych prób. Spróbuj ponownie za {$wait} s."
            );
        }

        $data['attempts'] = array_values(
            array_filter($data['attempts'], fn(int $t) => $t > time() - self::WINDOW_SECONDS)
        );

        if (count($data['attempts']) >= self::MAX_ATTEMPTS) {
            $data['locked_until'] = time() + self::LOCKOUT_SECONDS;
            self::save($file, $data);
            throw new RuntimeException(
                'Zbyt wiele nieudanych prób logowania. Dostęp zablokowany na 15 minut.'
            );
        }

        $data['attempts'][] = time();
        self::save($file, $data);
    }

    public static function clear(string $action, string $identifier): void
    {
        $file = self::filePath($action, $identifier);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    private static function filePath(string $action, string $identifier): string
    {
        $hash = hash('sha256', $action . '|' . $identifier);
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rl_' . $hash . '.json';
    }

    private static function load(string $file): array
    {
        if (!file_exists($file)) {
            return ['attempts' => [], 'locked_until' => 0];
        }
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : ['attempts' => [], 'locked_until' => 0];
    }

    private static function save(string $file, array $data): void
    {
        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
