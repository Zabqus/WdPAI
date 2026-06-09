# SyncU — Study Planner

Aplikacja webowa do zarządzania nauką: kursy, wydarzenia, zadania, notatki, plan nauki i współdzielenie zasobów między użytkownikami.

---

## Technologie

| Warstwa | Technologia |
|---------|-------------|
| Backend | PHP 8.2 OOP, bez frameworka, własny MVC + Router |
| Baza danych | PostgreSQL 16 (PDO, widoki, triggery, funkcje, transakcje) |
| Frontend | HTML5, CSS3 (media queries), JavaScript + Fetch API |
| Serwer | Nginx (reverse proxy → PHP-FPM) |
| Konteneryzacja | Docker + Docker Compose |

---

## Wymagania

- [Docker](https://docs.docker.com/get-docker/) ≥ 24
- [Docker Compose](https://docs.docker.com/compose/) ≥ 2 (wbudowany w Docker Desktop)
- Port **8080** wolny (aplikacja), **5433** (PostgreSQL), **5050** (pgAdmin)

---

## Uruchomienie

### 1. Sklonuj repozytorium

```bash
git clone <url-repozytorium>
cd wdpai
```

### 2. Skonfiguruj zmienne środowiskowe

```bash
cp .env.example .env
```

Otwórz `.env` i zmień hasła na własne (szczegóły w sekcji [Konfiguracja .env](#konfiguracja-env)).

### 3. Uruchom kontenery

```bash
docker compose up --build -d
```

Pierwsze uruchomienie pobiera obrazy i buduje kontenery (~2–3 minuty).  
Skrypt `docker/db/init/init.sql` tworzy schemat bazy, a `seed.sql` wgrywa dane testowe.

### 4. Otwórz aplikację

| Adres | Opis |
|-------|------|
| http://localhost:8080 | Aplikacja |
| http://localhost:5050 | pgAdmin (zarządzanie bazą) |

### 5. Zatrzymaj kontenery

```bash
docker compose down          # zatrzymuje kontenery, dane bazy zostają
docker compose down -v       # zatrzymuje i usuwa wolumeny (reset bazy)
```

---

## Konfiguracja .env

Plik `.env` jest odczytywany przez Docker Compose i przekazywany do kontenerów `db` oraz `pgadmin`.

```env
# Połączenie z PostgreSQL (używane wewnątrz sieci Docker)
POSTGRES_HOST=db               # nazwa serwisu w docker-compose — nie zmieniać
POSTGRES_PORT=5432             # port wewnętrzny kontenera — nie zmieniać
POSTGRES_DB=syncu              # nazwa bazy danych
POSTGRES_USER=syncu_user       # użytkownik bazy
POSTGRES_PASSWORD=...          # hasło — ZMIEŃ na własne, silne hasło

# Konto administratora pgAdmin (panel webowy na porcie 5050)
PGADMIN_DEFAULT_EMAIL=admin@example.com
PGADMIN_DEFAULT_PASSWORD=...   # ZMIEŃ na własne hasło
```

> **Uwaga:** plik `.env` zawiera dane uwierzytelniające — nie commituj go do repozytorium.  
> Szablon bez haseł jest w `.env.example`.

---

## Dane testowe (seed)

Po uruchomieniu bazy dostępne są trzy konta:

| Login (email) | Hasło | Rola |
|---------------|-------|------|
| `jan@example.com` | `Student1234!` | user |
| `anna@example.com` | `Student1234!` | user |
| `admin@example.com` | `Admin1234!` | admin |

Użytkownik `jan` ma 2 kursy, 6 wydarzeń, 16 zadań, 4 notatki i 7 wpisów w planie nauki.  
Użytkownik `anna` ma 1 kurs z 2 wydarzeniami. Istnieją przykładowe udostępnienia między nimi.

---

## Testy

### PHPUnit (testy jednostkowe)

```bash
# Zainstaluj zależności (jednorazowo, wymaga PHP + Composer lokalnie lub w kontenerze)
docker exec -it wdpai-php-1 bash -c "cd /app && composer install"

# Uruchom testy
docker exec -it wdpai-php-1 bash -c "cd /app && vendor/bin/phpunit"
```

Testy działają **bez połączenia z bazą** (mocki PHPUnit + ReflectionClass).

| Plik | Liczba testów | Co testuje |
|------|--------------|------------|
| `tests/AuthServiceTest.php` | 6 | Walidacja rejestracji (puste pola, format email, siła hasła, duplikat) |
| `tests/StudyProgressGroupingTest.php` | 2 | Algorytm grupowania planu nauki, obliczanie `plan_pct` |

### Testy endpointów (curl/bash)

Wymagana działająca aplikacja (`docker compose up -d`).

```bash
bash tests/endpoints.sh http://localhost:8080
```

Skrypt testuje kolejno: dostępność stron, ochronę tras (401/302/403), logowanie, CSRF, API po zalogowaniu.

---

## Architektura

```
Przeglądarka
    │  HTTP (Fetch API / form submit)
    ▼
Nginx : 8080
    │  FastCGI
    ▼
PHP-FPM
    │
    ├── index.php → Routing.php
    │       │
    │       ├── AuthGuard   (sesja)
    │       ├── RoleGuard   (rola admin)
    │       └── CsrfGuard   (token CSRF)
    │
    ├── Controllers/      ← obsługa żądań HTTP
    ├── Services/         ← logika biznesowa
    ├── Repository/       ← dostęp do danych (PDO)
    ├── Entity/           ← niemutowalne obiekty domenowe
    └── Views/            ← szablony PHP (HTML)
            │
            ▼
        PostgreSQL : 5432
```

Wzorce: **MVC**, **Repository**, **Service Layer**, **Singleton** (Database), **Value Object** (Entity).

---

## Moduły

| Moduł | Ścieżka | Opis |
|-------|---------|------|
| Autoryzacja | `/login`, `/register`, `/logout` | Rejestracja, logowanie z rate limitingiem |
| Kursy | `/courses`, `/api/courses` | CRUD kursów z kolorami |
| Wydarzenia | `/events`, `/api/events` | CRUD kolokwiów/egzaminów, zmiana statusu |
| Zadania | `/api/tasks` | Checklista, toggle done, reorder (drag), trigger aktualizuje event |
| Notatki | `/notes`, `/api/notes` | CRUD notatek powiązanych z kursem/wydarzeniem |
| Plan nauki | `/study-plan`, `/api/study-plan` | Przypisanie zadań do dni |
| Postęp | `/api/study-progress` | % ukończenia per wydarzenie, plan dzienny |
| Współdzielenie | `/groups`, `/api/shares/*` | Udostępnianie events/notatek z poziomem read/edit |
| Kalendarz | `/calendar`, `/api/events` | Widok miesięczny z Fetch API |
| Panel admina | `/admin` | Zarządzanie użytkownikami (rola, blokada, usunięcie) |

---

## Baza danych

### Tabele (9)
`users` · `user_profiles` · `courses` · `events` · `tasks` · `notes` · `study_plans` · `event_shares` · `note_shares`

### Elementy zaawansowane

| Element | Nazwa | Opis |
|---------|-------|------|
| Widok | `v_events_with_course` | JOIN events + courses + users |
| Widok | `v_event_progress` | Postęp wydarzeń z użyciem funkcji |
| Funkcja | `get_completion_pct(event_id)` | % ukończonych zadań dla wydarzenia |
| Trigger | `trg_update_event_status` | Auto-zmiana `events.is_done` po zmianie tasków |
| Trigger | `trg_create_user_profile` | Auto-tworzenie profilu po rejestracji (relacja 1:1) |
| Trigger | `trg_prevent_self_share` | Blokada udostępnienia samemu sobie (RAISE P0001) |
| Transakcja | `EventRepository::createWithTasks` | REPEATABLE READ — atomowe tworzenie event + taski |

### Relacje
- **1:1** `users` → `user_profiles`
- **1:N** `users→courses→events→tasks`, `users→notes`
- **M:N** `users ↔ events` (przez `event_shares`), `users ↔ notes` (przez `note_shares`)

Schemat w `docker/db/init/init.sql`, dane testowe w `docker/db/init/seed.sql`.

---

## Bezpieczeństwo

| Mechanizm | Szczegóły |
|-----------|-----------|
| CSRF | Token 64-znakowy, `hash_equals()`, obsługa form (`_csrf`) i AJAX (`X-CSRF-Token`) |
| Rate limiting | 5 prób / 15 min na IP, lockout 15 min (plik JSON w `/tmp`) |
| Sesje | Timeout 30 min, HttpOnly, SameSite=Lax, regeneracja ID po logowaniu |
| Hasła | bcrypt cost 12, wymóg: ≥8 znaków + litera + cyfra + znak specjalny |
| SQL Injection | PDO prepared statements, `ATTR_EMULATE_PREPARES=false` |
| XSS | `htmlspecialchars()` we wszystkich szablonach |
| Ownership | Weryfikacja `user_id` przed każdą modyfikacją zasobu |

---

## Struktura katalogów

```
.
├── docker/
│   ├── db/init/          # init.sql (schemat) + seed.sql (dane testowe)
│   ├── nginx/            # konfiguracja Nginx
│   └── php/              # Dockerfile + php.ini
├── public/
│   └── assets/           # CSS, JS, obrazy
├── src/
│   ├── controllers/      # 13 kontrolerów + Session, CsrfGuard, RateLimiter
│   ├── Entity/           # 6 klas domenowych
│   ├── Models/           # Database.php (Singleton PDO)
│   ├── Repository/       # 6 repozytoriów
│   ├── Services/         # 3 serwisy (Auth, Share, StudyProgress)
│   └── Views/            # szablony PHP
├── tests/
│   ├── AuthServiceTest.php
│   ├── StudyProgressGroupingTest.php
│   ├── bootstrap.php
│   ├── endpoints.sh      # smoke testy curl
│   └── scenario.md       # scenariusz testowy krok po kroku
├── .env.example
├── composer.json
├── docker-compose.yaml
├── index.php
├── phpunit.xml
└── Routing.php
```
