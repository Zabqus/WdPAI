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

    /** @return Task[] sorted by position then id */
    public function findByEventId(int $eventId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM tasks WHERE event_id = :eid ORDER BY position ASC, id ASC',
            ['eid' => $eventId]
        );
        return array_map(fn($r) => $this->map($r), $rows);
    }

    public function create(int $eventId, string $title, ?string $description = null): Task
    {
        $row = $this->db->fetchOne(
            'INSERT INTO tasks (event_id, title, description, position)
             VALUES (
                 :eid, :title, :desc,
                 COALESCE((SELECT MAX(position) + 1 FROM tasks WHERE event_id = :eid2), 0)
             )
             RETURNING id',
            ['eid' => $eventId, 'title' => $title, 'desc' => $description, 'eid2' => $eventId]
        );
        return $this->findById((int) $row['id']);
    }

    public function update(int $id, string $title): bool
    {
        return $this->db->execute(
            'UPDATE tasks SET title = :title WHERE id = :id',
            ['title' => $title, 'id' => $id]
        ) > 0;
    }

    public function setDone(int $id, bool $done): bool
    {
        return $this->db->execute(
            'UPDATE tasks SET is_done = :done WHERE id = :id',
            ['done' => $done, 'id' => $id]
        ) > 0;
    }

    /**
     * Assigns positions 0, 1, 2, … to tasks in $orderedIds order.
     * Only updates tasks belonging to $eventId (safety check per row).
     */
    public function reorder(int $eventId, array $orderedIds): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($orderedIds as $position => $taskId) {
                $this->db->execute(
                    'UPDATE tasks SET position = :pos WHERE id = :id AND event_id = :eid',
                    ['pos' => $position, 'id' => (int) $taskId, 'eid' => $eventId]
                );
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
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
            (int)  $row['position'],
                   $row['created_at']
        );
    }
}
