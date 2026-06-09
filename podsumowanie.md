# SyncU – Podsumowanie projektu (analiza pod wymagania)

---

## Technologie (wymagane: Docker, GIT, HTML5, CSS, JS z Fetch API, PHP OOP, PostgreSQL, bez frameworka)

| Wymaganie | Status |
|-----------|--------|
| Docker + docker-compose | ✅ – 4 serwisy: nginx, php, postgres, pgadmin |
| GIT | ✅ – repozytorium z historią commitów |
| HTML5 | ✅ – 16 szablonów widoków |
| CSS (responsywny, media queries) | ✅ – 11 plików CSS, breakpointy: 480/600/680/900/960/1280px |
| JavaScript + Fetch API | ✅ – 13 plików JS, modularny wrapper `Api` oparty na fetch |
| PHP OOP | ✅ – Controllers, Services, Repositories, Entities, brak strukturalnego kodu |
| PostgreSQL | ✅ – baza z schematem w init.sql + dane testowe w seed.sql |
| Brak frameworka / gotowych szablonów | ✅ – własny router, własny MVC, czysty PDO |

---

## Architektura (MVC / zapewniająca bezpieczeństwo)

Zastosowano wzorzec MVC z podziałem na warstwy:

- **Controller** (`src/Controllers/`) – 13 klas dziedziczących po `AppController`; obsługa żądań HTTP, walidacja wejścia, delegowanie do serwisów i repozytoriów
- **Model** (`src/Repository/`, `src/Services/`, `src/Models/`) – 6 repozytoriów, 3 serwisy, 1 klasa bazy danych (Singleton + PDO)
- **View** (`src/Views/`) – szablony PHP, dane przekazywane przez `render()` z izolowanym scope'em zmiennych
- **Entity** (`src/Entity/`) – 6 niemutowalnych klas domenowych (User, Course, Event, Task, Note, StudyPlan)
- **Routing** (`Routing.php`) – statyczny dispatcher z tablicą tras GET/POST, guardy autoryzacji i ról

Komunikacja frontend ↔ backend przez Fetch API (REST-like JSON endpoints).

---

## Bezpieczeństwo

| Mechanizm | Implementacja |
|-----------|---------------|
| Sesje | `Session.php` – timeout 30 min, HttpOnly, SameSite=Lax, regeneracja po logowaniu |
| CSRF | `CsrfGuard.php` – token 64-znakowy, `hash_equals()`, obsługa form + header X-CSRF-Token |
| Rate limiting | `RateLimiter.php` – 5 prób / 15 minut na IP, lockout 15 min |
| Hasła | bcrypt cost 12, wymagania: ≥8 znaków, litera + cyfra + znak specjalny |
| SQL Injection | PDO prepared statements z named placeholders, `ATTR_EMULATE_PREPARES => false` |
| XSS | `htmlspecialchars()` w szablonach, sanityzacja tokenu CSRF |
| Ownership checks | we wszystkich kontrolerach weryfikacja `user_id` przed modyfikacją zasobu |
| Role | user / admin – role guard w routerze, `requireAdmin()` w kontrolerze |

---

## Baza danych (PostgreSQL)

### Tabele (9)
`users`, `user_profiles`, `courses`, `events`, `tasks`, `notes`, `study_plans`, `event_shares`, `note_shares`

### Relacje – wszystkie typy spełnione
| Typ | Przykład |
|-----|---------|
| Jeden-do-jednego | `users` → `user_profiles` (ON DELETE CASCADE) |
| Jeden-do-wielu | `users→courses→events→tasks` |
| Wiele-do-wielu | `users ↔ events` przez `event_shares`, `users ↔ notes` przez `note_shares` |

### Widoki (2) ✅
- `v_events_with_course` – JOIN events + courses + users (3 tabele)
- `v_event_progress` – zdarzenia z liczbą tasków, ukończeniem, używa funkcji `get_completion_pct`

### Funkcja (1) ✅
- `get_completion_pct(p_event_id INT)` – oblicza % ukończenia tasków dla zdarzenia

### Wyzwalacze (3) ✅
- `trg_update_event_status` – AFTER INSERT/UPDATE ON tasks → ustawia `events.is_done` automatycznie
- `trg_create_user_profile` – AFTER INSERT ON users → tworzy wpis w `user_profiles` (relacja 1:1)
- `trg_prevent_self_share` – BEFORE INSERT ON event_shares/note_shares → blokuje udostępnianie sobie samemu (RAISE EXCEPTION P0001)

### Transakcje ✅
- `EventRepository::createWithTasks()` – poziom izolacji **REPEATABLE READ**, atomowo tworzy event + listę tasków
- `TaskRepository::reorder()` – transakcja przy bulk update pozycji

### Klucze obce – akcje ✅
- `ON DELETE CASCADE` – users→courses→events→tasks, users→notes, users→user_profiles
- `ON DELETE SET NULL` – notes.event_id, notes.course_id (notatka przeżywa usunięcie rodzica)

### Normalizacja
- **1NF** – atomowe wartości, brak wielowartościowych atrybutów
- **2NF** – wszystkie atrybuty zależą od całego klucza głównego
- **3NF** – brak zależności przechodnich, brak redundancji

### Plik SQL
`docker/db/init/init.sql` (schemat, 284 linie) + `docker/db/init/seed.sql` (dane testowe, 112 linii) ✅

---

## Elementy aplikacji

### Logowanie / sesja / uprawnienia
- Rejestracja z walidacją siły hasła ✅
- Logowanie z rate limitingiem ✅
- Utrzymanie sesji (30 min inactivity timeout) ✅
- Wylogowanie ✅
- Role: user / admin z weryfikacją w runtime ✅

### Zarządzanie użytkownikami (admin)
- Panel `/admin` – lista użytkowników ✅
- Zmiana roli (user/admin) ✅
- Blokowanie/odblokowanie konta (`is_active`) ✅
- Usuwanie użytkownika ✅
- Zabezpieczenie przed samomodyfikacją ✅

### Wybrana funkcjonalność (CRUD + współdzielenie)
- **Courses** – tworzenie, edycja, usuwanie, lista z kolorami ✅
- **Events** – CRUD, toggle status, filtrowanie po kursie/miesiącu ✅
- **Tasks** – CRUD, toggle done, reorder pozycji (drag-style), wyzwalacz aktualizuje event ✅
- **Notes** – CRUD, opcjonalne powiązanie z event/kursem, lista filtrowana ✅
- **Study Plan** – planowanie tasków na konkretne dni, widok dzienny ✅
- **Sharing** – udostępnianie events i notatek innym użytkownikom po emailu, poziomy read/edit ✅
- **Dashboard** – dzisiejszy plan, nadchodzące eventy (max 4), progress kursów ✅
- **Calendar** – widok miesięczny, dynamiczne pobieranie przez Fetch API ✅

---

## OOP i SOLID

### Wzorce użyte
- **Singleton** – `Database.php` (jedna instancja połączenia)
- **Repository Pattern** – 6 repozytoriów, separacja dostępu do danych
- **Service Layer** – 3 serwisy, logika biznesowa poza kontrolerami
- **Value Object / Entity** – 6 niemutowalnych klas domenowych (tylko gettery)
- **Template Method** – `AppController` jako baza dla wszystkich kontrolerów

### SOLID
| Zasada | Jak spełniona |
|--------|--------------|
| SRP | każde repozytorium obsługuje jedną encję; każdy serwis jedną domenę |
| OCP | kontrolery rozszerzają `AppController`; serwisy przyjmują repo przez konstruktor |
| LSP | wszystkie repo mają spójny interfejs; kontrolery wymienne |
| ISP | klasy mają tylko potrzebne metody |
| DIP | kontrolery zależą od repozytoriów (nie od bazy); serwisy od repozytoriów |

---

## Frontend

- HTML5 szablony z częściami współdzielonymi (`partials/head.php`, `navbar.php`, `sidebar.php`) ✅
- 11 plików CSS z media queries (mobile-first od 480px do 1280px) ✅
- Flexbox + CSS Grid dla layoutów ✅
- 13 plików JS, modularny `Api` wrapper z Fetch API ✅
- Dynamiczne aktualizowanie danych bez przeładowania strony (AJAX) ✅
- Modale dla operacji CRUD ✅
- Walidacja po stronie frontendu + backendu ✅

---

## Obsługa błędów

- `ErrorHandler.php` – strony 400, 401, 403, 404, 500 ✅
- Opisy błędów po polsku ✅
- AJAX: JSON z właściwym HTTP status code (422, 404, 403, 401, 500) ✅
- Routing zwraca 404 na nieznane ścieżki, 400 na błędną metodę HTTP, 403 na błędny CSRF ✅

---

## Docker / DevOps

- `docker-compose.yaml` – 4 serwisy (nginx, php, postgres, pgadmin) ✅
- `.env.example` z opisanymi zmiennymi środowiskowymi ✅
- Dedykowane `Dockerfile` dla nginx i php ✅
- Init scripts montowane do kontenera bazy ✅

---

## Czego BRAKUJE (według wymagań)

### Wymagane i nieobecne

| Brakujący element | Dlaczego ważne |
|-------------------|----------------|
| **README.md** | Wymagane explicite; ma zawierać: opis projektu, instrukcję uruchomienia (`docker-compose up`), zmienne środowiskowe, diagramy |
| **Diagram ERD** (PNG/SVG + źródło .drawio) | Jawne wymaganie w dokumentacji |
| **Diagram architektury** (warstwowy) | Krótki schemat MVC/layers – wymagany w readme |
| **Screeny aplikacji** (desktop + mobile) | Wymagane – wersja webowa i mobilna |
| **Scenariusz testowy** | Krok po kroku: logowanie, role, CRUD, błąd 403/401, widoki/wyzwalacze |
| **Checklista ukończonych elementów** | Jawne wymaganie w readme.md |
| **Testy PHPUnit** (1–2 testy) | Wymagane – choćby symboliczne testy serwisów/repozytoriów |
| **Testy integracyjne** (curl/bash) | Wymagane – prosty skrypt testujący endpointy |

### Elementy potencjalnie do doprecyzowania

- **Strona `groups.php`** – aktualnie widok udostępnionych zasobów (notatki/eventy od innych); może wymagać dopracowania UX
- **Widok profilu** – ograniczony (wyświetla dane, brak edycji); nie jest to wymagane, ale warto wiedzieć
- **`study-plan.css`** – plik o nazwie `.js` w wykazie CSS (literówka w pliku, bez wpływu na działanie)

---

## Ocena projektu

### Co jest naprawdę dobre
1. **Architektura kodu** – czyste MVC, dobra separacja warstw, brak spaghetti, Repository + Service + Entity to profesjonalne podejście
2. **Baza danych** – kompletna: wszystkie typy relacji, 2 widoki, 3 triggery, funkcja, transakcje z poziomem izolacji, 3NF, właściwe FK actions
3. **Bezpieczeństwo** – ponadprzeciętne jak na projekt studencki: CSRF, rate limiting, session fixation prevention, bcrypt cost 12, ownership checks wszędzie
4. **Fetch API** – poprawnie użyte, z centralnym wrapperem, CSRF token w headerze, obsługa błędów
5. **Responsywność** – media queries w każdym pliku CSS, praktyczne breakpointy
6. **Objętość funkcjonalna** – 7 modułów (kursy, eventy, taski, notatki, plan nauki, sharing, admin), co jak na projekt studencki jest bardzo dużo

### Co obniży ocenę
1. **Brak README.md** – to jest wymóg konieczny, egzaminator nie powinien musieć zgadywać jak uruchomić projekt
2. **Brak testów** – PHPUnit + curl/bash to jawne wymaganie; "choćby symboliczne" oznacza że 2 testy wystarczą, ale muszą być
3. **Brak diagramów** – ERD i diagram architektury to dokumentacja wizualna; bez tego trudno ocenić świadomość projektu
4. **Brak screenów** – wymagane w readme

### Szacunkowa ocena (na podstawie spełnienia wymagań)

```
Kod i architektura:     bardzo dobrze (spełnione w całości, ponadprogramowe)
Baza danych:            bardzo dobrze (spełnione w całości)
Bezpieczeństwo/OOP:     bardzo dobrze
Responsywność/frontend: dobrze
Testy:                  niedostatecznie (brak)
Dokumentacja (readme):  niedostatecznie (brak README, brak ERD, brak screenów)
```

**Wniosek**: Projekt jest technicznie silny i spełnia niemal wszystkie wymagania *implementacyjne*. Główne ryzyko oceny to brakująca dokumentacja i testy — to są jawnie wymienione wymagania konieczne. Dodanie README (1–2 godziny), diagramu ERD (30 minut w draw.io) i 2–3 testów PHPUnit (1–2 godziny) przeniosłoby projekt w strefę bardzo wysokiej oceny.
