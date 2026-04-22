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

    public function create(int $courseId, string $title, ?string $description, string $type, string $startAt, ?string $endAt): Event
    {
        $this->db->execute(
            'INSERT INTO events (course_id, title, description, type, start_at, end_at)
             VALUES (:cid, :title, :desc, :type, :start, :end)',
            ['cid' => $courseId, 'title' => $title, 'desc' => $description,
             'type' => $type, 'start' => $startAt, 'end' => $endAt]
        );
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, string $title, ?string $description, string $type, string $startAt, ?string $endAt): bool
    {
        return $this->db->execute(
            'UPDATE events SET title = :title, description = :desc, type = :type, start_at = :start, end_at = :end WHERE id = :id',
            ['title' => $title, 'desc' => $description, 'type' => $type,
             'start' => $startAt, 'end' => $endAt, 'id' => $id]
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
