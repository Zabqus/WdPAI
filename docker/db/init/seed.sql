-- ============================================================
--  Share Planner – dane testowe
--  Hasła (bcrypt, cost 12):
--    admin  → Admin1234!
--    jan    → Student1234!
--    anna   → Student1234!
-- ============================================================

-- ------------------------------------------------------------
--  USERS  (trigger trg_user_profile tworzy user_profiles auto)
-- ------------------------------------------------------------

INSERT INTO users (username, email, password, role) VALUES
    ('admin', 'admin@example.com', '$2y$12$N2vImdnZFh03d0N4YyhOuu7A68gsjGpoG9PHOdvSihUj4MAcLrPKO', 'admin'),
    ('jan',   'jan@example.com',   '$2y$12$GZLj0WeHaZcjJ5EbOq4wY.XRHdFuVEy5MOUvVTpLB4GAogxW32ZLW', 'user'),
    ('anna',  'anna@example.com',  '$2y$12$GZLj0WeHaZcjJ5EbOq4wY.XRHdFuVEy5MOUvVTpLB4GAogxW32ZLW', 'user');

-- ------------------------------------------------------------
--  COURSES
-- ------------------------------------------------------------

-- jan (user_id=2): 2 kursy
INSERT INTO courses (user_id, name, description, color) VALUES
    (2, 'Matematyka',  'Analiza matematyczna i algebra liniowa', '#e74c3c'),
    (2, 'Bazy Danych', 'Relacyjne bazy danych i SQL',            '#3498db');

-- anna (user_id=3): 1 kurs
INSERT INTO courses (user_id, name, description, color) VALUES
    (3, 'Algorytmy', 'Algorytmy i struktury danych', '#2ecc71');

-- ------------------------------------------------------------
--  EVENTS  (course_id: 1=Matematyka, 2=Bazy Danych, 3=Algorytmy)
-- ------------------------------------------------------------

INSERT INTO events (course_id, title, description, type, start_at, end_at) VALUES
    -- Matematyka
    (1, 'Kolokwium z analizy',  'Całki, szeregi, granice',      'colloquium', '2026-05-10 10:00+02', '2026-05-10 12:00+02'),
    (1, 'Egzamin końcowy MAT',  'Całość materiału semestru',    'exam',       '2026-06-25 09:00+02', '2026-06-25 11:00+02'),
    -- Bazy Danych
    (2, 'Kolokwium ze złączeń', 'JOIN, podzapytania, widoki',   'colloquium', '2026-05-18 12:00+02', '2026-05-18 13:30+02'),
    (2, 'Egzamin końcowy BD',   'Normalizacja, transakcje, SQL','exam',       '2026-06-20 09:00+02', '2026-06-20 11:00+02'),
    -- Algorytmy
    (3, 'Kolokwium z grafów',   'BFS, DFS, Dijkstra',           'colloquium', '2026-05-15 12:00+02', '2026-05-15 13:30+02'),
    (3, 'Projekt końcowy ALG',  'Implementacja algorytmu',       'other',      '2026-06-10 10:00+02', '2026-06-10 12:00+02');

-- ------------------------------------------------------------
--  TASKS  (event_id: 1-6 jak wyżej)
-- ------------------------------------------------------------

INSERT INTO tasks (event_id, title, is_done) VALUES
    -- Kolokwium z analizy
    (1, 'Powtórz całki oznaczone',         TRUE),
    (1, 'Rozwiąż zadania z szeregów',      FALSE),
    (1, 'Przejrzyj granice funkcji',       FALSE),
    -- Egzamin MAT
    (2, 'Powtórz algebrę liniową',         FALSE),
    (2, 'Zrób testy z poprzednich lat',    FALSE),
    -- Kolokwium ze złączeń
    (3, 'Ćwiczenia z INNER JOIN',          TRUE),
    (3, 'Ćwiczenia z LEFT/RIGHT JOIN',     TRUE),
    (3, 'Podzapytania skorelowane',        FALSE),
    -- Egzamin BD
    (4, 'Normalizacja do 3NF – ćwiczenia', FALSE),
    (4, 'Transakcje i poziomy izolacji',   FALSE),
    (4, 'Zapytania SQL – zestawy zadań',   FALSE),
    -- Kolokwium z grafów
    (5, 'BFS i DFS – implementacja',       TRUE),
    (5, 'Algorytm Dijkstry',               FALSE),
    -- Projekt ALG
    (6, 'Wybór tematu projektu',           TRUE),
    (6, 'Implementacja rozwiązania',       FALSE),
    (6, 'Przygotowanie prezentacji',       FALSE);

-- ------------------------------------------------------------
--  NOTES
-- ------------------------------------------------------------

INSERT INTO notes (user_id, event_id, course_id, title, content) VALUES
    (2, 1, 1, 'Wzory całkowe',      'Całka z sin(x) = -cos(x) + C, całka z cos(x) = sin(x) + C ...'),
    (2, 3, 2, 'Rodzaje JOIN',       'INNER JOIN – część wspólna; LEFT JOIN – wszystko z lewej + część wspólna ...'),
    (2, 4, 2, 'Poziomy izolacji',   'READ COMMITTED, REPEATABLE READ, SERIALIZABLE – różnice i zastosowania.'),
    (3, 5, 3, 'Złożoność grafów',   'BFS: O(V+E), DFS: O(V+E), Dijkstra: O((V+E) log V)');

-- ------------------------------------------------------------
--  STUDY PLANS
-- ------------------------------------------------------------

INSERT INTO study_plans (user_id, task_id, planned_date) VALUES
    (2, 2,  '2026-05-05'),
    (2, 3,  '2026-05-06'),
    (2, 8,  '2026-05-12'),
    (2, 9,  '2026-06-15'),
    (2, 10, '2026-06-16'),
    (3, 13, '2026-05-08'),
    (3, 15, '2026-06-01');

-- ------------------------------------------------------------
--  SHARING  (anna udostępnia janowi, jan udostępnia annie)
-- ------------------------------------------------------------

-- anna udostępnia janowi event "Kolokwium z grafów" (event_id=5)
INSERT INTO event_shares (user_id, event_id, access) VALUES
    (2, 5, 'read');

-- jan udostępnia annie notatkę "Rodzaje JOIN" (note_id=2) z prawem edycji
INSERT INTO note_shares (user_id, note_id, access) VALUES
    (3, 2, 'edit');

-- jan udostępnia annie notatkę "Poziomy izolacji" (note_id=3)
INSERT INTO note_shares (user_id, note_id, access) VALUES
    (3, 3, 'read');
