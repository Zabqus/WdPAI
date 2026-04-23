<?php

class Database {

    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $host   = $_ENV['POSTGRES_HOST'] ?? 'db';
        $port   = $_ENV['POSTGRES_PORT'] ?? '5432';
        $dbname = $_ENV['POSTGRES_DB']   ?? 'syncu';
        $user   = $_ENV['POSTGRES_USER'] ?? 'syncu_user';
        $pass   = $_ENV['POSTGRES_PASSWORD'] ?? '';

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(string $isolationLevel = 'READ COMMITTED'): void
    {
        $this->pdo->exec("SET TRANSACTION ISOLATION LEVEL $isolationLevel");
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup(): never
    {
        throw new \Exception('Database singleton cannot be unserialized.');
    }
}
