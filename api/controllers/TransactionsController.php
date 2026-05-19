<?php
declare(strict_types=1);

namespace Api\Controllers;

use App\Config\Database;
use App\Enums\JwtAlgorithm;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use PDO;

class TransactionsController
{
    private Transaction $transactionModel;
    private User $userModel;
    private Product $productModel;
    private PDO $connection;
    private string $key;

    public function __construct(
        ?Transaction $transactionModel = null,
        ?User $userModel = null,
        ?Product $productModel = null,
        ?PDO $connection = null,
    ) {
        $this->connection = $connection ?? (new Database())->getConnection();
        $this->transactionModel = $transactionModel ?? new Transaction($this->connection);
        $this->userModel = $userModel ?? new User($this->connection);
        $this->productModel = $productModel ?? new Product($this->connection);
        $this->key = $this->getJwtKey();
    }

    public function createTransaction(): void
    {
        $token = $this->getBearerToken();
        if (!$token) {
            $this->sendResponse(401, 'Token is missing.');
            return;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->key, JwtAlgorithm::HS256->value));
            if (UserRole::tryFrom($decoded->role) !== UserRole::USER) {
                $this->sendResponse(403, 'Forbidden: Only users can create transactions.');
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $userId = (int) $decoded->sub;
            $productId = $this->positiveInt($data['product_id'] ?? null, 'Product ID');
            $quantity = $this->positiveInt($data['quantity'] ?? null, 'Quantity');

            $product = $this->productModel->get($productId);
            if (!$this->userModel->get($userId) || !$product) {
                $this->sendResponse(400, 'Invalid user or product.');
                return;
            }
            if ($quantity <= 0 || (int) $product['quantity_available'] < $quantity) {
                $this->sendResponse(422, 'Insufficient product quantity.');
                return;
            }

            $this->connection->beginTransaction();
            $totalPrice = (float) $product['price'] * $quantity;
            if (!$this->productModel->decrementStock($productId, $quantity)) {
                $this->connection->rollBack();
                $this->sendResponse(422, 'Insufficient product quantity.');
                return;
            }
            $this->transactionModel->create($userId, $productId, $quantity, $totalPrice);
            $this->connection->commit();

            $this->sendResponse(201, 'Transaction created successfully.');
        } catch (\Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            $this->sendResponse($e instanceof InvalidArgumentException ? 422 : 500, 'Error: ' . htmlspecialchars($e->getMessage()));
        }
    }

    private function getBearerToken(): ?string
    {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $authorization = $headers['Authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? null);

        if ($authorization) {
            preg_match('/Bearer\s(\S+)/', $authorization, $matches);
            return $matches[1] ?? null;
        }

        return null;
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

    private function positiveInt(mixed $value, string $field): int
    {
        if (is_bool($value)) {
            throw new InvalidArgumentException(sprintf('%s must be an integer between 1 and 2147483647.', $field));
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 2147483647],
        ]);

        if ($integer === false) {
            throw new InvalidArgumentException(sprintf('%s must be an integer between 1 and 2147483647.', $field));
        }

        return $integer;
    }
}
