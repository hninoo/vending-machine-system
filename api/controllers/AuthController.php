<?php
declare(strict_types=1);

namespace Api\Controllers;

use App\Enums\JwtAlgorithm;
use App\Enums\UserRole;
use App\Models\User;
use Firebase\JWT\JWT;

class AuthController
{
    private User $userModel;
    private string $key;

    public function __construct(?User $userModel = null)
    {
        $this->userModel = $userModel ?? new User();
        $this->key = $this->getJwtKey();
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $username = (string) ($data['username'] ?? '');
            $password = (string) ($data['password'] ?? '');
            $user = $this->userModel->findByUsername($username);

            $role = $user ? UserRole::tryFrom($user['role']) : null;

            if ($user && $role && password_verify($password, $user['password'])) {
                $token = $this->generateJwt((int) $user['id'], $role);
                $this->sendResponse(200, ['token' => $token]);
            } else {
                $this->sendResponse(401, "Invalid credentials.");
            }

        } catch (\Exception $e) {
            $this->sendResponse(500, "Error: " . htmlspecialchars($e->getMessage()));
        }
    }

    public function logout(): void
    {
        $this->sendResponse(200, ['message' => 'Logged out successfully. Please delete the token on the client side.']);
    }

    private function generateJwt(int $userId, UserRole $role): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => $userId,
            'role' => $role->value,
        ];

        return JWT::encode($payload, $this->key, JwtAlgorithm::HS256->value);
    }

    private function getJwtKey(): string
    {
        $config = include __DIR__ . '/../config/jwt.php';
        return $config['key'];
    }

    private function sendResponse(int $statusCode, mixed $data): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
