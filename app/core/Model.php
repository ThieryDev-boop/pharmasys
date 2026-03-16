<?php
// app/core/Model.php
class Model {
    protected PDO $pdo;

    public function __construct() {
        static $instance = null;
        if ($instance === null) {
            $config = require CONFIG_PATH . '/database.php';
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        }
        $this->pdo = $instance;
    }

    // Requête SELECT avec requêtes préparées (protection injection SQL)
    protected function query(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function queryOne(string $sql, array $params = []): array|false {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    protected function execute(string $sql, array $params = []): bool {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    protected function insert(string $sql, array $params = []): string {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    protected function beginTransaction(): void { $this->pdo->beginTransaction(); }
    protected function commit(): void           { $this->pdo->commit(); }
    protected function rollback(): void         { $this->pdo->rollBack(); }
}
