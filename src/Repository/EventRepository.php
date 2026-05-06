<?php

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Entity/Event.php';

class EventRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?Event
    {
        $row = $this->db->fetchOne(
            'SELECT * FROM events WHERE id = :id',
            ['id' => $id]
        );
        return $row ? $this->map($row) : null;
    }

    /** @return Event[] */
    public function findByCourseId(int $courseId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM events WHERE course_id = :cid ORDER BY start_at',
            ['cid' => $courseId]
        );
        return array_map(fn($r) => $this->map($r), $rows);
    }

    /** @return Event[] — wszystkie eventy użytkownika przez JOIN z courses */
    public function findByUserId(int $userId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT e.* FROM events e
             JOIN courses c ON e.course_id = c.id
             WHERE c.user_id = :uid
             ORDER BY e.start_at',
            ['uid' => $userId]
        );
        return array_map(fn($r) => $this->map($r), $rows);
    }

    /**
     * Wiersze z widoku v_events_with_course dla danego użytkownika.
     * @return array[]
     */
    public function findWithCourseByUserId(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM v_events_with_course WHERE user_id = :uid ORDER BY start_at',
            ['uid' => $userId]
        );
    }

    /**
     * Wiersze z widoku v_events_with_course dla danego użytkownika i miesiąca (UTC).
     * @return array[]
     */
    public function findWithCourseByUserIdAndMonth(int $userId, int $year, int $month): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM v_events_with_course
             WHERE user_id = :uid
               AND EXTRACT(YEAR  FROM start_at AT TIME ZONE 'UTC') = :year
               AND EXTRACT(MONTH FROM start_at AT TIME ZONE 'UTC') = :month
             ORDER BY start_at",
            ['uid' => $userId, 'year' => $year, 'month' => $month]
        );
    }

    /**
     * Wiersze z widoku v_events_with_course dla danego kursu.
     * @return array[]
     */
    public function findWithCourseByCourseId(int $courseId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM v_events_with_course WHERE course_id = :cid ORDER BY start_at',
            ['cid' => $courseId]
        );
    }

    /** @return array|null */
    public function findWithCourseById(int $eventId): ?array
    {
        $row = $this->db->fetchOne(
            'SELECT * FROM v_events_with_course WHERE event_id = :id',
            ['id' => $eventId]
        );
        return $row ?: null;
    }

    public function create(int $courseId, string $title, ?string $description, string $type, string $startAt, ?string $endAt): Event
    {
        $row = $this->db->fetchOne(
            'INSERT INTO events (course_id, title, description, type, start_at, end_at)
             VALUES (:cid, :title, :desc, :type, :start, :end)
             RETURNING id',
            ['cid' => $courseId, 'title' => $title, 'desc' => $description,
             'type' => $type, 'start' => $startAt, 'end' => $endAt]
        );
        return $this->findById((int) $row['id']);
    }

    /**
     * Tworzy event wraz z listą tasków w jednej transakcji (REPEATABLE READ).
     * @param string[] $taskTitles
     */
    public function createWithTasks(
        int     $courseId,
        string  $title,
        ?string $description,
        string  $type,
        string  $startAt,
        ?string $endAt,
        array   $taskTitles
    ): Event {
        $this->db->beginTransaction('REPEATABLE READ');

        try {
            $row = $this->db->fetchOne(
                'INSERT INTO events (course_id, title, description, type, start_at, end_at)
                 VALUES (:cid, :title, :desc, :type, :start, :end)
                 RETURNING id',
                ['cid' => $courseId, 'title' => $title, 'desc' => $description,
                 'type' => $type, 'start' => $startAt, 'end' => $endAt]
            );
            $eventId = (int) $row['id'];

            foreach ($taskTitles as $taskTitle) {
                if (trim($taskTitle) === '') continue;
                $this->db->execute(
                    'INSERT INTO tasks (event_id, title) VALUES (:eid, :title)',
                    ['eid' => $eventId, 'title' => trim($taskTitle)]
                );
            }

            $this->db->commit();
            return $this->findById($eventId);
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function update(int $id, int $courseId, string $title, ?string $description, string $type, string $startAt, ?string $endAt): bool
    {
        return $this->db->execute(
            'UPDATE events
             SET course_id = :cid, title = :title, description = :desc,
                 type = :type, start_at = :start, end_at = :end
             WHERE id = :id',
            ['cid' => $courseId, 'title' => $title, 'desc' => $description,
             'type' => $type, 'start' => $startAt, 'end' => $endAt, 'id' => $id]
        ) > 0;
    }

    public function setDone(int $id, bool $isDone): bool
    {
        return $this->db->execute(
            'UPDATE events SET is_done = :done WHERE id = :id',
            ['done' => $isDone, 'id' => $id]
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute(
            'DELETE FROM events WHERE id = :id',
            ['id' => $id]
        ) > 0;
    }

    private function map(array $row): Event
    {
        return new Event(
            (int)  $row['id'],
            (int)  $row['course_id'],
                   $row['title'],
                   $row['description'],
                   $row['type'],
                   $row['start_at'],
                   $row['end_at'],
            (bool) $row['is_done'],
                   $row['created_at']
        );
    }
}
