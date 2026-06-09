<?php

/**
 * Bootstrap dla testów jednostkowych — ładuje klasy źródłowe bez uruchamiania aplikacji.
 * Połączenie z bazą danych NIE jest nawiązywane (singleton Database nie jest wywoływany).
 */

// AuthService chainuje: UserRepository → Database, User, RateLimiter
require_once __DIR__ . '/../src/Services/AuthService.php';

// StudyProgressService → Database (singleton, nie wywoływany w konstruktorze w testach)
require_once __DIR__ . '/../src/Services/StudyProgressService.php';
