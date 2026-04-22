<?php

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Entity/Task.php';

class TaskRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?Task
    {
        $row = $this->db->fetchOne(
            'SELECT * FROM tasks WHERE id = :id',
            ['id' => $id]
        );
        return $row ? $this->map($row) : null;
    }

    /** @return Task[] */
    public function findByEventId(int $eventId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM tasks WHERE event_id = :eid ORDER BY created_at',
            ['eid' => $eventId]
        );
        return array_map(fn($r) => $this->map($r), $rows);
    }

    public function create(int $eventId, string $title, ?string $description = null): Task
    {
        $this->db->execute(
            'INSERT INTO tasks (event_id, title, description) VALUES (:eid, :title, :desc)',
            ['eid' => $eventId, 'title' => $title, 'desc' => $description]
        );
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function setDone(int $id, bool $done): bool
    {
        return $this->db->execute(
            'UPDATE tasks SET is_done = :done WHERE id = :id',
            ['done' => $done, 'id' => $id]
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute(
            'DELETE FROM tasks WHERE id = :id',
            ['id' => $id]
        ) > 0;
    }

    private function map(array $row): Task
    {
        return new Task(
            (int)  $row['id'],
            (int)  $row['event_id'],
                   $row['title'],
                   $row['description'],
            (bool) $row['is_done'],
                   $row['created_at']
        );
    }
}
