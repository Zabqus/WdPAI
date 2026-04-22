-- ============================================================
--  Share Planner – schemat bazy danych
-- ============================================================

-- ------------------------------------------------------------
--  TYPY WYLICZENIOWE
-- ------------------------------------------------------------

CREATE TYPE user_role    AS ENUM ('user', 'admin');
CREATE TYPE event_type   AS ENUM ('exam', 'colloquium', 'other');
CREATE TYPE access_level AS ENUM ('read', 'edit');

-- ------------------------------------------------------------
--  TABELE
-- ------------------------------------------------------------

-- 1. users
CREATE TABLE users (
    id          SERIAL PRIMARY KEY,
    username    VARCHAR(50)  UNIQUE NOT NULL,
    email       VARCHAR(255) UNIQUE NOT NULL,
    password    TEXT NOT NULL,
    role        user_role DEFAULT 'user',
    created_at  TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    is_active   BOOLEAN DEFAULT TRUE
);

-- 1:1  profil/statystyki użytkownika
CREATE TABLE user_profiles (
    user_id     INT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    avatar_url  TEXT,
    bio         TEXT,
    updated_at  TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 1:N  User -> Course
CREATE TABLE courses (
    id          SERIAL PRIMARY KEY,
    user_id     INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    color       VARCHAR(7) DEFAULT '#6c63ff',
    created_at  TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 1:N  Course -> Event
CREATE TABLE events (
    id          SERIAL PRIMARY KEY,
    course_id   INT NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    type        event_type DEFAULT 'other',
    start_at    TIMESTAMPTZ NOT NULL,
    end_at      TIMESTAMPTZ,
    is_done     BOOLEAN DEFAULT FALSE,
    created_at  TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 1:N  Event -> Task
CREATE TABLE tasks (
    id          SERIAL PRIMARY KEY,
    event_id    INT NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    is_done     BOOLEAN DEFAULT FALSE,
    created_at  TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 1:N  Event -> Note  (opcjonalnie też Course -> Note)
CREATE TABLE notes (
    id          SERIAL PRIMARY KEY,
    user_id     INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    event_id    INT REFERENCES events(id) ON DELETE SET NULL,
    course_id   INT REFERENCES courses(id) ON DELETE SET NULL,
    title       VARCHAR(255) NOT NULL,
    content     TEXT,
    created_at  TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Plan nauki – przypisanie tasków do konkretnych dni
CREATE TABLE study_plans (
    id           SERIAL PRIMARY KEY,
    user_id      INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    task_id      INT NOT NULL REFERENCES tasks(id) ON DELETE CASCADE,
    planned_date DATE NOT NULL,
    created_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, task_id, planned_date)
);

-- M:N  User <-> Event  (współdzielenie)
CREATE TABLE event_shares (
    user_id      INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    event_id     INT NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    access       access_level DEFAULT 'read',
    shared_at    TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, event_id)
);

-- M:N  User <-> Note  (współdzielenie)
CREATE TABLE note_shares (
    user_id      INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    note_id      INT NOT NULL REFERENCES notes(id) ON DELETE CASCADE,
    access       access_level DEFAULT 'read',
    shared_at    TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, note_id)
);

-- ------------------------------------------------------------
--  WIDOKI
-- ------------------------------------------------------------

-- Widok 1: wydarzenia z przedmiotem i właścicielem
CREATE VIEW view_events_full AS
SELECT
    e.id          AS event_id,
    e.title       AS event_title,
    e.type,
    e.start_at,
    e.end_at,
    e.is_done,
    c.id          AS course_id,
    c.name        AS course_name,
    c.color       AS course_color,
    u.id          AS user_id,
    u.username,
    u.email
FROM events e
JOIN courses c ON e.course_id = c.id
JOIN users   u ON c.user_id   = u.id;

-- Widok 2: postęp nauki (% ukończonych tasków per event)
CREATE VIEW view_event_progress AS
SELECT
    e.id          AS event_id,
    e.title       AS event_title,
    c.name        AS course_name,
    u.id          AS user_id,
    COUNT(t.id)                                        AS total_tasks,
    COUNT(t.id) FILTER (WHERE t.is_done)               AS done_tasks,
    ROUND(
        COUNT(t.id) FILTER (WHERE t.is_done)::NUMERIC
        / NULLIF(COUNT(t.id), 0) * 100, 2
    )                                                  AS progress_pct
FROM events  e
JOIN courses c ON e.course_id = c.id
JOIN users   u ON c.user_id   = u.id
LEFT JOIN tasks t ON t.event_id = e.id
GROUP BY e.id, e.title, c.name, u.id;

-- ------------------------------------------------------------
--  FUNKCJA: procent ukończenia eventu
-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION get_event_progress(p_event_id INT)
RETURNS NUMERIC AS $$
DECLARE
    total INT;
    done  INT;
BEGIN
    SELECT COUNT(*), COUNT(*) FILTER (WHERE is_done)
    INTO total, done
    FROM tasks
    WHERE event_id = p_event_id;

    RETURN CASE WHEN total = 0 THEN 0
                ELSE ROUND(done::NUMERIC / total * 100, 2)
           END;
END;
$$ LANGUAGE plpgsql;

-- ------------------------------------------------------------
--  WYZWALACZ: auto-zakończenie eventu gdy wszystkie taski done
-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION trg_check_event_done()
RETURNS TRIGGER AS $$
DECLARE
    total INT;
    done  INT;
BEGIN
    SELECT COUNT(*), COUNT(*) FILTER (WHERE is_done)
    INTO total, done
    FROM tasks
    WHERE event_id = NEW.event_id;

    IF total > 0 AND total = done THEN
        UPDATE events SET is_done = TRUE  WHERE id = NEW.event_id;
    ELSE
        UPDATE events SET is_done = FALSE WHERE id = NEW.event_id;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_event_done
AFTER INSERT OR UPDATE OF is_done ON tasks
FOR EACH ROW EXECUTE FUNCTION trg_check_event_done();

-- ------------------------------------------------------------
--  DANE PRZYKŁADOWE
-- ------------------------------------------------------------

INSERT INTO users (username, email, password, role) VALUES
    ('admin',   'admin@example.com',   '$2y$12$placeholder_admin_hash',  'admin'),
    ('jan',     'jan@example.com',     '$2y$12$placeholder_jan_hash',    'user'),
    ('anna',    'anna@example.com',    '$2y$12$placeholder_anna_hash',   'user');

INSERT INTO user_profiles (user_id) VALUES (1), (2), (3);

INSERT INTO courses (user_id, name, color) VALUES
    (2, 'Matematyka',         '#e74c3c'),
    (2, 'Bazy Danych',        '#3498db'),
    (3, 'Algorytmy',          '#2ecc71');

INSERT INTO events (course_id, title, type, start_at, end_at) VALUES
    (1, 'Kolokwium z analizy',   'colloquium', '2026-05-10 10:00+02', '2026-05-10 12:00+02'),
    (2, 'Egzamin końcowy',       'exam',       '2026-06-20 09:00+02', '2026-06-20 11:00+02'),
    (3, 'Kolokwium z grafów',    'colloquium', '2026-05-15 12:00+02', '2026-05-15 13:30+02');

INSERT INTO tasks (event_id, title) VALUES
    (1, 'Powtórz całki'),
    (1, 'Rozwiąż zadania z szeregów'),
    (2, 'Normalizacja do 3NF'),
    (2, 'Zapytania SQL – ćwiczenia'),
    (3, 'BFS i DFS – implementacja');

INSERT INTO notes (user_id, event_id, course_id, title, content) VALUES
    (2, 1, 1, 'Wzory całkowe', 'Całka z sin(x) = -cos(x) + C ...'),
    (2, 2, 2, 'Widoki SQL',    'CREATE VIEW ... SELECT ... JOIN ...');

INSERT INTO event_shares (user_id, event_id, access) VALUES
    (3, 1, 'read');

INSERT INTO note_shares (user_id, note_id, access) VALUES
    (3, 1, 'read');
