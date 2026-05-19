<?php
declare(strict_types=1);

namespace Web\Controllers;

use App\Enums\HttpMethod;
use App\Enums\UserRole;
use App\Models\User;
use InvalidArgumentException;
use Web\Traits\AdminCheckTrait;

class UsersController
{
    use AdminCheckTrait;

    private User $userModel;

    public function __construct(?User $userModel = null)
    {
        $this->userModel = $userModel ?? new User();
    }

    public function index(): void
    {
        $this->checkAdmin();

        try {
            $limit = 5; 
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $offset = ($page - 1) * $limit;
    
            $users = $this->userModel->all($limit, $offset);
            $totalUsers = $this->userModel->count();
            $totalPages = ceil($totalUsers / $limit);
            require __DIR__ . '/../views/user/list.php';
        } catch (\Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function create(): void
    {
        $this->checkAdmin();

        try {
            if ($_SERVER['REQUEST_METHOD'] === HttpMethod::POST->value) {
                $username = trim((string) ($_POST['username'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $role = UserRole::tryFrom((string) ($_POST['role'] ?? ''));

                if (empty($username)) {
                    throw new InvalidArgumentException("Username is required.");
                }
                if (empty($password)) {
                    throw new InvalidArgumentException("Password is required.");
                }
                if (!$role) {
                    throw new InvalidArgumentException("Role is required.");
                }

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $this->userModel->create($username, $hashedPassword, $role);

                $this->ensureSession();
                $_SESSION['success_message'] = "User created successfully!";
                
                header('Location: /users');
                exit();
            }

            require __DIR__ . '/../views/user/add.php';
        } catch (\Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function edit(int $id): void
    {
        $this->checkAdmin();

        try {
            $user = $this->userModel->get($id);

            if ($_SERVER['REQUEST_METHOD'] === HttpMethod::POST->value) {
                $username = trim((string) ($_POST['username'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $role = UserRole::tryFrom((string) ($_POST['role'] ?? ''));

                if (empty($username)) {
                    throw new InvalidArgumentException("Username is required.");
                }
                if (!$role) {
                    throw new InvalidArgumentException("Role is required.");
                }

                $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : $user['password'];
                $this->userModel->update($id, $username, $hashedPassword, $role);

                $this->ensureSession();
                $_SESSION['success_message'] = "User updated successfully!";
                
                header('Location: /users');
                exit();
            }

            require __DIR__ . '/../views/user/edit.php';
        } catch (\Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    public function delete(int $id): void
    {
        $this->checkAdmin();

        try {
            if ($id <= 0) {
                throw new InvalidArgumentException("Invalid ID provided");
            }

            $this->userModel->delete($id);

            $this->ensureSession();
            $_SESSION['success_message'] = "User deleted successfully!";
            
            header('Location: /users');
            exit();
        } catch (\Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
            exit();
        }
    }

    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}
