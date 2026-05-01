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
    password    TEXT         NOT NULL,
    role        user_role    NOT NULL DEFAULT 'user',
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_active   BOOLEAN      NOT NULL DEFAULT TRUE,

    CONSTRAINT chk_username_length CHECK (LENGTH(TRIM(username)) >= 3),
    CONSTRAINT chk_email_format    CHECK (email ~* '^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$'),
    CONSTRAINT chk_password_length CHECK (LENGTH(password) >= 8)
);

-- 1:1  profil/statystyki użytkownika
CREATE TABLE user_profiles (
    user_id     INT         PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    avatar_url  TEXT,
    bio         VARCHAR(500),
    updated_at  TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 1:N  User -> Course
CREATE TABLE courses (
    id          SERIAL PRIMARY KEY,
    user_id     INT          NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    color       VARCHAR(7)   NOT NULL DEFAULT '#6c63ff',
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_course_name  CHECK (LENGTH(TRIM(name)) > 0),
    CONSTRAINT chk_course_color CHECK (color ~* '^#[0-9a-f]{6}$')
);

-- 1:N  Course -> Event
CREATE TABLE events (
    id          SERIAL PRIMARY KEY,
    course_id   INT          NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    type        event_type   NOT NULL DEFAULT 'other',
    start_at    TIMESTAMPTZ  NOT NULL,
    end_at      TIMESTAMPTZ,
    is_done     BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_event_title   CHECK (LENGTH(TRIM(title)) > 0),
    CONSTRAINT chk_event_dates   CHECK (end_at IS NULL OR end_at > start_at)
);

-- 1:N  Event -> Task
CREATE TABLE tasks (
    id          SERIAL PRIMARY KEY,
    event_id    INT          NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    is_done     BOOLEAN      NOT NULL DEFAULT FALSE,
    position    INT          NOT NULL DEFAULT 0,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_task_title CHECK (LENGTH(TRIM(title)) > 0)
);

-- 1:N  Event -> Note  (opcjonalnie też Course -> Note)
CREATE TABLE notes (
    id          SERIAL PRIMARY KEY,
    user_id     INT          NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    event_id    INT          REFERENCES events(id)  ON DELETE SET NULL,
    course_id   INT          REFERENCES courses(id) ON DELETE SET NULL,
    title       VARCHAR(255) NOT NULL,
    content     TEXT,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_note_title CHECK (LENGTH(TRIM(title)) > 0)
);

-- Plan nauki – przypisanie tasków do konkretnych dni
CREATE TABLE study_plans (
    id           SERIAL PRIMARY KEY,
    user_id      INT         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    task_id      INT         NOT NULL REFERENCES tasks(id) ON DELETE CASCADE,
    planned_date DATE        NOT NULL,
    created_at   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (user_id, task_id, planned_date)
);

-- M:N  User <-> Event  (współdzielenie)
CREATE TABLE event_shares (
    user_id      INT          NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    event_id     INT          NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    access       access_level NOT NULL DEFAULT 'read',
    shared_at    TIMESTAMPTZ  NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id, event_id)
);

-- M:N  User <-> Note  (współdzielenie)
CREATE TABLE note_shares (
    user_id      INT          NOT NULL REFERENCES users(id)  ON DELETE CASCADE,
    note_id      INT          NOT NULL REFERENCES notes(id)  ON DELETE CASCADE,
    access       access_level NOT NULL DEFAULT 'read',
    shared_at    TIMESTAMPTZ  NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id, note_id)
);

-- ------------------------------------------------------------
--  INDEKSY
-- ------------------------------------------------------------

CREATE INDEX idx_courses_user_id    ON courses     (user_id);
CREATE INDEX idx_events_course_id   ON events      (course_id);
CREATE INDEX idx_tasks_event_id     ON tasks       (event_id);
CREATE INDEX idx_tasks_position     ON tasks       (event_id, position);
CREATE INDEX idx_notes_user_id      ON notes       (user_id);
CREATE INDEX idx_notes_event_id     ON notes       (event_id);
CREATE INDEX idx_notes_course_id    ON notes       (course_id);
CREATE INDEX idx_study_plans_user   ON study_plans (user_id);
CREATE INDEX idx_study_plans_task   ON study_plans (task_id);
CREATE INDEX idx_events_start_at    ON events      (start_at);

-- ------------------------------------------------------------
--  FUNKCJA: procent ukończenia eventu
-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION get_completion_pct(p_event_id INT)
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
--  WIDOKI
-- ------------------------------------------------------------

-- Widok 1: wydarzenia z przedmiotem i właścicielem
CREATE VIEW v_events_with_course AS
SELECT
    e.id          AS event_id,
    e.title       AS event_title,
    e.description AS event_description,
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

-- Widok 2: postęp nauki – używa funkcji get_completion_pct
CREATE VIEW v_event_progress AS
SELECT
    e.id                        AS event_id,
    e.title                     AS event_title,
    c.name                      AS course_name,
    u.id                        AS user_id,
    COUNT(t.id)                 AS total_tasks,
    COUNT(t.id) FILTER (WHERE t.is_done) AS done_tasks,
    get_completion_pct(e.id)    AS progress_pct
FROM events  e
JOIN courses c ON e.course_id = c.id
JOIN users   u ON c.user_id   = u.id
LEFT JOIN tasks t ON t.event_id = e.id
GROUP BY e.id, e.title, c.name, u.id;

-- ------------------------------------------------------------
--  WYZWALACZ: auto-update statusu eventu po zmianie tasków
-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION trg_update_event_status()
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

CREATE TRIGGER trg_event_status
AFTER INSERT OR UPDATE OF is_done ON tasks
FOR EACH ROW EXECUTE FUNCTION trg_update_event_status();

-- ------------------------------------------------------------
--  WYZWALACZ: auto-tworzenie profilu po rejestracji usera
-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION trg_create_user_profile()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO user_profiles (user_id) VALUES (NEW.id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_user_profile
AFTER INSERT ON users
FOR EACH ROW EXECUTE FUNCTION trg_create_user_profile();

-- ------------------------------------------------------------
--  WYZWALACZ: blokada self-share (event i note)
-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION trg_prevent_self_share()
RETURNS TRIGGER AS $$
DECLARE
    owner_id INT;
BEGIN
    IF TG_TABLE_NAME = 'event_shares' THEN
        SELECT c.user_id INTO owner_id
        FROM events e
        JOIN courses c ON e.course_id = c.id
        WHERE e.id = NEW.event_id;
    ELSIF TG_TABLE_NAME = 'note_shares' THEN
        SELECT user_id INTO owner_id
        FROM notes
        WHERE id = NEW.note_id;
    END IF;

    IF NEW.user_id = owner_id THEN
        RAISE EXCEPTION 'Nie możesz udostępnić zasobu samemu sobie.';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_no_self_share_event
BEFORE INSERT ON event_shares
FOR EACH ROW EXECUTE FUNCTION trg_prevent_self_share();

CREATE TRIGGER trg_no_self_share_note
BEFORE INSERT ON note_shares
FOR EACH ROW EXECUTE FUNCTION trg_prevent_self_share();

-- dane testowe znajdują się w seed.sql
