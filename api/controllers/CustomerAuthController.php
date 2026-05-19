<?php
declare(strict_types=1);

namespace Api\Controllers;

use App\Enums\UserRole;
use App\Models\User;

class CustomerAuthController
{
    private const SESSION_KEY = 'customer_user_id';
    private const RESERVED_USERNAMES = ['public-customer'];
    private const MAX_USERNAME_LENGTH = 100;

    private User $userModel;

    public function __construct(?User $userModel = null)
    {
        $this->userModel = $userModel ?? new User();
    }

    public function register(): void
    {
        $payload = $this->jsonPayload();
        $username = $this->stringInput($payload['username'] ?? null);
        $password = $this->stringInput($payload['password'] ?? null);

        if ($username === '' || strlen($username) < 3) {
            $this->respond(422, ['ok' => false, 'message' => 'Username must be at least 3 characters.']);
            return;
        }
        if ($this->length($username) > self::MAX_USERNAME_LENGTH) {
            $this->respond(422, ['ok' => false, 'message' => 'Username must be 100 characters or fewer.']);
            return;
        }
        if (strlen($password) < 6) {
            $this->respond(422, ['ok' => false, 'message' => 'Password must be at least 6 characters.']);
            return;
        }
        if (in_array(strtolower($username), self::RESERVED_USERNAMES, true)) {
            $this->respond(422, ['ok' => false, 'message' => 'That username is reserved.']);
            return;
        }
        if ($this->userModel->findByUsername($username)) {
            $this->respond(409, ['ok' => false, 'message' => 'That username is already taken.']);
            return;
        }

        try {
            $userId = $this->userModel->register($username, $password, UserRole::USER);
            $_SESSION[self::SESSION_KEY] = $userId;

            $this->respond(201, [
                'ok' => true,
                'message' => 'Welcome aboard.',
                'user' => ['id' => $userId, 'username' => $username, 'role' => UserRole::USER->value],
            ]);
        } catch (\Throwable $e) {
            $this->respond(500, ['ok' => false, 'message' => 'Could not create account.']);
        }
    }

    public function login(): void
    {
        $payload = $this->jsonPayload();
        $username = $this->stringInput($payload['username'] ?? null);
        $password = $this->stringInput($payload['password'] ?? null);

        if ($username === '' || $password === '') {
            $this->respond(422, ['ok' => false, 'message' => 'Username and password are required.']);
            return;
        }

        $user = $this->userModel->findByUsername($username);
        if (!$user || !password_verify($password, (string) $user['password'])) {
            $this->respond(401, ['ok' => false, 'message' => 'Invalid credentials.']);
            return;
        }
        if (in_array(strtolower((string) $user['username']), self::RESERVED_USERNAMES, true)) {
            $this->respond(401, ['ok' => false, 'message' => 'Invalid credentials.']);
            return;
        }

        $_SESSION[self::SESSION_KEY] = (int) $user['id'];

        $this->respond(200, [
            'ok' => true,
            'message' => 'Welcome back.',
            'user' => [
                'id' => (int) $user['id'],
                'username' => (string) $user['username'],
                'role' => (string) $user['role'],
            ],
        ]);
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        $this->respond(200, ['ok' => true, 'message' => 'Signed out.']);
    }

    public function me(): void
    {
        $userId = $_SESSION[self::SESSION_KEY] ?? null;
        if (!$userId) {
            $this->respond(200, ['ok' => true, 'user' => null]);
            return;
        }

        $user = $this->userModel->get((int) $userId);
        if (!$user) {
            unset($_SESSION[self::SESSION_KEY]);
            $this->respond(200, ['ok' => true, 'user' => null]);
            return;
        }

        $this->respond(200, [
            'ok' => true,
            'user' => [
                'id' => (int) $user['id'],
                'username' => (string) $user['username'],
                'role' => (string) $user['role'],
            ],
        ]);
    }

    private function jsonPayload(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function respond(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit();
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }

    private function stringInput(mixed $value): string
    {
        return is_scalar($value) && ! is_bool($value) ? trim((string) $value) : '';
    }
}
