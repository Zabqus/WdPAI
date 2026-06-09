# SyncU — Scenariusz testowy (krok po kroku)

Scenariusz opisuje ręczne testowanie wszystkich głównych funkcji aplikacji.  
**Wymagania:** aplikacja uruchomiona (`docker compose up -d`), baza zasilona danymi z `seed.sql`.  
**URL:** http://localhost:8080

---

## 1. Rejestracja nowego użytkownika

**Cel:** weryfikacja walidacji i poprawnej rejestracji.

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 1.1 | Przejdź na `/register` | Wyświetla się formularz rejestracji |
| 1.2 | Wyślij pusty formularz | Komunikat: „Wypełnij wszystkie pola." |
| 1.3 | Wpisz email bez `@` (np. `test`) | Komunikat: „Podaj prawidłowy adres e-mail." |
| 1.4 | Wpisz hasło krótsze niż 8 znaków | Komunikat: „Hasło musi mieć co najmniej 8 znaków." |
| 1.5 | Wpisz hasło bez znaku specjalnego (np. `Password1`) | Komunikat: „Hasło musi zawierać co najmniej jeden znak specjalny." |
| 1.6 | Wpisz różne hasła w polach „hasło" i „potwierdź" | Komunikat: „Hasła nie są identyczne." |
| 1.7 | Wypełnij poprawnie: imię `Test`, nazwisko `User`, email `test@example.com`, hasło `Test1234!` | Przekierowanie na `/login`, konto utworzone |
| 1.8 | Powtórz rejestrację z tym samym emailem | Komunikat: „Ten adres e-mail jest już zajęty." |

---

## 2. Logowanie

**Cel:** weryfikacja mechanizmu uwierzytelniania i rate limitingu.

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 2.1 | Przejdź na `/login` | Wyświetla się formularz logowania |
| 2.2 | Wyślij pusty formularz | Komunikat: „Wypełnij wszystkie pola." |
| 2.3 | Wpisz poprawny email, błędne hasło | Komunikat: „Nieprawidłowy e-mail lub hasło." |
| 2.4 | Powtórz krok 2.3 łącznie 5 razy | Po 5. próbie: „Zbyt wiele nieudanych prób. Dostęp zablokowany na 15 minut." |
| 2.5 | Zaloguj się poprawnymi danymi: `jan@example.com` / `Student1234!` | Przekierowanie na `/dashboard` |
| 2.6 | Sprawdź, że sesja utrzymuje się po odświeżeniu strony | Dashboard dostępny bez ponownego logowania |
| 2.7 | Kliknij „Wyloguj" | Przekierowanie na `/login`, sesja zakończona |
| 2.8 | Wróć do `/dashboard` bez logowania | Przekierowanie na `/login` (auth guard) |

---

## 3. Kursy — CRUD

**Zaloguj się jako `jan@example.com` / `Student1234!`**

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 3.1 | Przejdź na `/courses` | Widoczne 2 kursy: Matematyka, Bazy Danych |
| 3.2 | Kliknij „Dodaj kurs", wpisz nazwę `Fizyka`, wybierz kolor | Nowy kurs pojawia się na liście |
| 3.3 | Edytuj kurs `Fizyka`, zmień nazwę na `Fizyka Kwantowa` | Lista zaktualizowana |
| 3.4 | Usuń kurs `Fizyka Kwantowa` | Kurs znika z listy |

---

## 4. Wydarzenia (Events) — CRUD i statusy

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 4.1 | Przejdź na `/events` | Widoczne istniejące wydarzenia powiązane z kursami jana |
| 4.2 | Utwórz wydarzenie: kurs `Matematyka`, tytuł `Kolokwium próbne`, typ `colloquium`, data za 7 dni | Wydarzenie pojawia się na liście |
| 4.3 | Edytuj wydarzenie — zmień tytuł | Lista zaktualizowana |
| 4.4 | Dodaj 3 zadania do wydarzenia (sekcja Tasks wewnątrz widoku) | Zadania pojawiają się na checkliście |
| 4.5 | Oznacz jedno zadanie jako ukończone (`toggle done`) | Trigger DB automatycznie aktualizuje `events.is_done`, progress bar rośnie |
| 4.6 | Oznacz wszystkie zadania jako ukończone | Wydarzenie automatycznie zmienia status na `done` (trigger) |
| 4.7 | Usuń wydarzenie | Znika z listy, powiązane zadania usunięte (CASCADE) |

---

## 5. Plan nauki (Study Plan)

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 5.1 | Przejdź na `/study-plan` | Widoczny plan nauki |
| 5.2 | Dodaj zadanie do planu na dziś | Zadanie pojawia się w sekcji „Dzisiaj" |
| 5.3 | Sprawdź `/api/study-progress/plan` | JSON z planem na dziś, obliczony `plan_pct` |
| 5.4 | Oznacz zadanie w planie jako ukończone | `plan_pct` wzrasta |
| 5.5 | Usuń zadanie z planu | Znika z planu, pozostaje w checkliście wydarzenia |

---

## 6. Notatki — CRUD

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 6.1 | Przejdź na `/notes` | Widoczne notatki jana |
| 6.2 | Utwórz notatkę: tytuł `Wzory całkowania`, treść, powiązanie z kursem `Matematyka` | Notatka pojawia się na liście |
| 6.3 | Edytuj notatkę — zmień treść | Zmiany zapisane |
| 6.4 | Usuń notatkę | Znika z listy |

---

## 7. Współdzielenie zasobów (Sharing)

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 7.1 | Zaloguj się jako `jan@example.com` |  |
| 7.2 | Przejdź na `/groups` lub widok sharing przy wydarzeniu | Widoczna sekcja udostępniania |
| 7.3 | Udostępnij wydarzenie użytkownikowi `anna@example.com` z dostępem `read` | Anna pojawia się na liście uczestników |
| 7.4 | Spróbuj udostępnić samemu sobie (`jan@example.com`) | Komunikat błędu: „Nie możesz udostępnić zasobu samemu sobie." (trigger DB P0001) |
| 7.5 | Zaloguj się jako `anna@example.com` / `Student1234!` | |
| 7.6 | Przejdź na `/groups` | Widoczne wydarzenie udostępnione przez jana |
| 7.7 | Wróć jako `jan`, zmień dostęp anny na `edit`, następnie cofnij udostępnienie | Zmiany widoczne na liście |

---

## 8. Kalendarz

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 8.1 | Przejdź na `/calendar` | Widok miesięczny, dni z wydarzeniami oznaczone kolorami kursów |
| 8.2 | Kliknij strzałki „poprzedni / następny miesiąc" | Fetch API pobiera dane dynamicznie, brak przeładowania strony |
| 8.3 | Kliknij w dzień z wydarzeniem | Wyświetla się szczegół wydarzenia |

---

## 9. Panel administratora

**Zaloguj się jako `admin@example.com` / `Admin1234!`**

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 9.1 | Przejdź na `/admin` | Lista wszystkich użytkowników |
| 9.2 | Zmień rolę `jan` na `admin`, następnie z powrotem na `user` | Rola zaktualizowana w tabeli |
| 9.3 | Zablokuj konto `test@example.com` (konto z kroku 1.7) | Pole `is_active = false` |
| 9.4 | Wyloguj się, spróbuj zalogować się na zablokowane konto | Komunikat: „Konto jest nieaktywne. Skontaktuj się z administratorem." |
| 9.5 | Zaloguj ponownie jako admin, odblokuj konto | Konto aktywne |
| 9.6 | Usuń konto `test@example.com` | Znika z listy, powiązane dane usunięte (CASCADE) |

---

## 10. Kontrola dostępu i bezpieczeństwo

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 10.1 | Wyloguj się. Otwórz `/dashboard` w przeglądarce | Przekierowanie na `/login` (302) |
| 10.2 | Wyślij żądanie GET `/api/courses` z nagłówkiem `X-Requested-With: XMLHttpRequest` bez sesji | HTTP 401 Unauthorized |
| 10.3 | Zalogowany user (`jan`), otwórz `/admin` | HTTP 403 Forbidden |
| 10.4 | Wyślij POST `/courses/create` bez tokenu `_csrf` | HTTP 403 Forbidden (CsrfGuard) |
| 10.5 | Otwórz nieistniejącą ścieżkę, np. `/xyz` | HTTP 404 Not Found |
| 10.6 | Sprawdź nagłówki odpowiedzi (`Content-Security-Policy`, `X-Frame-Options`) w DevTools | Nagłówki bezpieczeństwa obecne |

---

## 11. Testy automatyczne

| Krok | Akcja | Oczekiwany wynik |
|------|-------|-----------------|
| 11.1 | `docker exec -it wdpai-php-1 bash -c "cd /app && composer install"` | Instalacja PHPUnit |
| 11.2 | `docker exec -it wdpai-php-1 bash -c "cd /app && vendor/bin/phpunit"` | 8 testów, 0 błędów |
| 11.3 | `bash tests/endpoints.sh http://localhost:8080` | Wszystkie scenariusze curl zaliczone |
