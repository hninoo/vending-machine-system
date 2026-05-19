<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Enums\UserRole;
use InvalidArgumentException;
use PDO;

class User
{
    private const MAX_USERNAME_LENGTH = 100;
    private const MAX_PASSWORD_LENGTH = 255;

    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? (new Database())->getConnection();
    }

    public function all(int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT id, username, role FROM users ORDER BY id ASC";
        if ($limit > 0) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $statement = $this->connection->prepare($sql);

        if ($limit > 0) {
            $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
            $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
        }

        $statement->execute();
        return $statement->fetchAll();
    }

    public function count(): int
    {
        $statement = $this->connection->query("SELECT COUNT(*) as total FROM users");
        return (int) $statement->fetch()['total'];
    }

    public function create(string $username, string $password, UserRole $role): string
    {
        $this->validateCredentials($username, $password);

        $statement = $this->connection->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $statement->execute([':username' => $username, ':password' => $password, ':role' => $role->value]);

        return $this->connection->lastInsertId();
    }

    public function get(int $id): array|false
    {
        $statement = $this->connection->prepare(
            "SELECT id, username, password, role
             FROM users
             WHERE id = :id"
        );
        $statement->execute([':id' => $id]);
        return $statement->fetch();
    }

    public function findByUsername(string $username): array|false
    {
        $statement = $this->connection->prepare(
            "SELECT id, username, password, role
             FROM users
             WHERE username = :username"
        );
        $statement->execute([':username' => $username]);
        return $statement->fetch();
    }

    public function update(int $id, string $username, string $password, UserRole $role): void
    {
        $this->validateUsername($username);
        if ($password !== '') {
            $this->validatePassword($password);
        }

        $sql = "UPDATE users SET username = :username, role = :role";
        $params = [':username' => $username, ':role' => $role->value, ':id' => $id];

        if ($password !== '') {
            $sql .= ", password = :password";
            $params[':password'] = $password;
        }

        $sql .= " WHERE id = :id";
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
    }

    public function delete(int $id): void
    {
        $statement = $this->connection->prepare("DELETE FROM users WHERE id = :id");
        $statement->execute([':id' => $id]);
    }

    public function register(string $username, string $password, UserRole $role): int
    {
        $this->validateUsername($username);

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $statement = $this->connection->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $statement->execute([':username' => $username, ':password' => $passwordHash, ':role' => $role->value]);

        return (int) $this->connection->lastInsertId();
    }

    private function validateCredentials(string $username, string $password): void
    {
        $this->validateUsername($username);
        $this->validatePassword($password);
    }

    private function validateUsername(string $username): void
    {
        $length = function_exists('mb_strlen') ? mb_strlen($username) : strlen($username);
        if ($length === 0 || $length > self::MAX_USERNAME_LENGTH) {
            throw new InvalidArgumentException('Username must be between 1 and 100 characters.');
        }
    }

    private function validatePassword(string $password): void
    {
        $length = function_exists('mb_strlen') ? mb_strlen($password) : strlen($password);
        if ($length === 0 || $length > self::MAX_PASSWORD_LENGTH) {
            throw new InvalidArgumentException('Password value must be between 1 and 255 characters.');
        }
    }
}
