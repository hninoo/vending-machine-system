<?php
declare(strict_types=1);

namespace App\Config;

use App\Exceptions\ConnectionException;
use PDO;
use PDOException;

final class Database
{
    private readonly string $host;
    private readonly string $dbName;
    private readonly string $username;
    private readonly string $password;
    private ?PDO $conn = null;

    public function __construct()
    {
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->dbName = getenv('DB_DATABASE') ?: 'vending_machine';
        $this->username = getenv('DB_USERNAME') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: 'root';
    }

    public function connect(): PDO
    {
        try {
            $this->conn = new PDO(
                sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $this->host, $this->dbName),
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );
        } catch (PDOException $e) {
            throw new ConnectionException('Database connection failed.', 0, $e);
        }

        return $this->conn;
    }

    public function getConnection(): PDO
    {
        return $this->conn ?? $this->connect();
    }
}
