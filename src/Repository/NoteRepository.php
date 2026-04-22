<?php

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Entity/Note.php';

class NoteRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?Note
    {
        $row = $this->db->fetchOne(
            'SELECT * FROM notes WHERE id = :id',
            ['id' => $id]
        );
        return $row ? $this->map($row) : null;
    }

    /** @return Note[] */
    public function findByUserId(int $userId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM notes WHERE user_id = :uid ORDER BY created_at DESC',
            ['uid' => $userId]
        );
        return array_map(fn($r) => $this->map($r), $rows);
    }

    /** @return Note[] */
    public function findByEventId(int $eventId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM notes WHERE event_id = :eid ORDER BY created_at DESC',
            ['eid' => $eventId]
        );
        return array_map(fn($r) => $this->map($r), $rows);
    }

    /** @return Note[] */
    public function findByCourseId(int $courseId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM notes WHERE course_id = :cid ORDER BY created_at DESC',
            ['cid' => $courseId]
        );
        return array_map(fn($r) => $this->map($r), $rows);
    }

    public function create(int $userId, string $title, ?string $content, ?int $eventId = null, ?int $courseId = null): Note
    {
        $this->db->execute(
            'INSERT INTO notes (user_id, event_id, course_id, title, content)
             VALUES (:uid, :eid, :cid, :title, :content)',
            ['uid' => $userId, 'eid' => $eventId, 'cid' => $courseId,
             'title' => $title, 'content' => $content]
        );
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, string $title, ?string $content): bool
    {
        return $this->db->execute(
            'UPDATE notes SET title = :title, content = :content WHERE id = :id',
            ['title' => $title, 'content' => $content, 'id' => $id]
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute(
            'DELETE FROM notes WHERE id = :id',
            ['id' => $id]
        ) > 0;
    }

    private function map(array $row): Note
    {
        return new Note(
            (int)  $row['id'],
            (int)  $row['user_id'],
                   $row['event_id']  !== null ? (int) $row['event_id']  : null,
                   $row['course_id'] !== null ? (int) $row['course_id'] : null,
                   $row['title'],
                   $row['content'],
                   $row['created_at']
        );
    }
}
