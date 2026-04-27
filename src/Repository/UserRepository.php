<?php

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Entity/User.php';

class UserRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?User
    {
        $row = $this->db->fetchOne(
            'SELECT * FROM users WHERE id = :id',
            ['id' => $id]
        );
        return $row ? $this->map($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->db->fetchOne(
            'SELECT * FROM users WHERE email = :email',
            ['email' => $email]
        );
        return $row ? $this->map($row) : null;
    }

    /** @return User[] */
    public function findAll(): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM users ORDER BY created_at DESC');
        return array_map(fn($r) => $this->map($r), $rows);
    }

    public function create(string $username, string $email, string $password, string $role = 'user'): User
    {
        $row = $this->db->fetchOne(
            'INSERT INTO users (username, email, password, role)
             VALUES (:username, :email, :password, :role)
             RETURNING id',
            ['username' => $username, 'email' => $email, 'password' => $password, 'role' => $role]
        );
        return $this->findById((int) $row['id']);
    }

    public function updateRole(int $id, string $role): bool
    {
        return $this->db->execute(
            'UPDATE users SET role = :role WHERE id = :id',
            ['role' => $role, 'id' => $id]
        ) > 0;
    }

    public function update(int $id, string $username, string $email): bool
    {
        return $this->db->execute(
            'UPDATE users SET username = :username, email = :email WHERE id = :id',
            ['username' => $username, 'email' => $email, 'id' => $id]
        ) > 0;
    }

    public function updatePassword(int $id, string $hashedPassword): bool
    {
        return $this->db->execute(
            'UPDATE users SET password = :password WHERE id = :id',
            ['password' => $hashedPassword, 'id' => $id]
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute(
            'DELETE FROM users WHERE id = :id',
            ['id' => $id]
        ) > 0;
    }

    public function setActive(int $id, bool $active): bool
    {
        return $this->db->execute(
            'UPDATE users SET is_active = :active WHERE id = :id',
            ['active' => $active, 'id' => $id]
        ) > 0;
    }

    private function map(array $row): User
    {
        return new User(
            (int)  $row['id'],
                   $row['username'],
                   $row['email'],
                   $row['password'],
                   $row['role'],
                   $row['created_at'],
            (bool) $row['is_active']
        );
    }
}
