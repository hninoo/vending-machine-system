<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Enums\UserRole;
use PDO;

class Admin
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? (new Database())->getConnection();
    }

    public function authenticate(string $username, string $password): array|false
    {
        $statement = $this->connection->prepare(
            "SELECT id, username, password, role
             FROM users
             WHERE username = :username AND role = :role"
        );
        $statement->execute([':username' => $username, ':role' => UserRole::ADMIN->value]);
        $admin = $statement->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }

        return false;
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public function isAdmin(): bool
    {
        return $this->isAuthenticated() && $_SESSION['role'] === UserRole::ADMIN->value;
    }
}
