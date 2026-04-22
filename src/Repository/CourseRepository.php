<?php

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Entity/Course.php';

class CourseRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?Course
    {
        $row = $this->db->fetchOne(
            'SELECT * FROM courses WHERE id = :id',
            ['id' => $id]
        );
        return $row ? $this->map($row) : null;
    }

    /** @return Course[] */
    public function findByUserId(int $userId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM courses WHERE user_id = :uid ORDER BY name',
            ['uid' => $userId]
        );
        return array_map(fn($r) => $this->map($r), $rows);
    }

    public function create(int $userId, string $name, ?string $description, string $color = '#6c63ff'): Course
    {
        $this->db->execute(
            'INSERT INTO courses (user_id, name, description, color) VALUES (:uid, :name, :desc, :color)',
            ['uid' => $userId, 'name' => $name, 'desc' => $description, 'color' => $color]
        );
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, string $name, ?string $description, string $color): bool
    {
        return $this->db->execute(
            'UPDATE courses SET name = :name, description = :desc, color = :color WHERE id = :id',
            ['name' => $name, 'desc' => $description, 'color' => $color, 'id' => $id]
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute(
            'DELETE FROM courses WHERE id = :id',
            ['id' => $id]
        ) > 0;
    }

    private function map(array $row): Course
    {
        return new Course(
            (int) $row['id'],
            (int) $row['user_id'],
                  $row['name'],
                  $row['description'],
                  $row['color'],
                  $row['created_at']
        );
    }
}
