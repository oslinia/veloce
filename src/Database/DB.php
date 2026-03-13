<?php

declare(strict_types=1);

namespace Veloce\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Обертка над PDO для удобной работы с базой данных.
 */
class DB
{
    private PDO $pdo;

    /**
     * @param array{dsn: string, user: string, pass: string, options?: array} $config
     */
    public function __construct(array $config)
    {
        try {
            $this->pdo = new PDO(
                $config['dsn'],
                $config['user'],
                $config['pass'],
                $config['options'] ?? [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Выполняет SQL-запрос с параметрами.
     * 
     * @param string $sql SQL запрос с плейсхолдерами (н-р: "SELECT * FROM users WHERE id = ?")
     * @param array<mixed> $params Параметры для подстановки.
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Возвращает одну строку из БД.
     */
    public function row(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Возвращает все строки.
     */
    public function all(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Возвращает ID последней вставленной записи.
     */
    public function lastInsertId(): string|false
    {
        return $this->pdo->lastInsertId();
    }
}
