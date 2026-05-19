<?php
declare(strict_types=1);

namespace Api\Controllers;

use App\Enums\JwtAlgorithm;
use App\Enums\UserRole;
use App\Models\Product;
use App\Support\ProductPayload;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;

class ProductsController
{
    private Product $productModel;
    private string $key;

    public function __construct(?Product $productModel = null)
    {
        $this->productModel = $productModel ?? new Product();
        $this->key = $this->getJwtKey();
    }

    public function getProducts(): void
    {
        try {
            $products = $this->productModel->all();
            $this->sendResponse(200, $products);
        } catch (\Exception $e) {
            $this->sendResponse(500, "Error: " . htmlspecialchars($e->getMessage()));
        }
    }

    public function getProduct(int $id): void
    {
        try {
            $product = $this->productModel->get($id);
            if ($product) {
                $this->sendResponse(200, $product);
            } else {
                $this->sendResponse(404, "Product not found.");
            }
        } catch (\Exception $e) {
            $this->sendResponse(500, "Error: " . htmlspecialchars($e->getMessage()));
        }
    }

    public function createProduct(): void
    {
        if (!$this->authorizeRole(UserRole::ADMIN)) {
            return;
        }

        try {
            $product = ProductPayload::fromArray($this->getJsonPayload());

            $id = $this->productModel->create(
                $product->name,
                $product->price,
                $product->quantity,
                $product->badge->value,
                $product->oldPrice,
            );
            $this->sendResponse(201, ['id' => (int) $id, 'message' => 'Product created successfully.']);
        } catch (\Exception $e) {
            $this->sendResponse($e instanceof InvalidArgumentException ? 422 : 500, 'Error: ' . htmlspecialchars($e->getMessage()));
        }
    }

    public function updateProduct(int $id): void
    {
        if (!$this->authorizeRole(UserRole::ADMIN)) {
            return;
        }

        try {
            $product = ProductPayload::fromArray($this->getJsonPayload());

            $this->productModel->updateDetails(
                $id,
                $product->name,
                $product->price,
                $product->quantity,
                $product->badge->value,
                $product->oldPrice,
            );
            $this->sendResponse(200, ['message' => 'Product updated successfully.']);
        } catch (\Exception $e) {
            $this->sendResponse($e instanceof InvalidArgumentException ? 422 : 500, 'Error: ' . htmlspecialchars($e->getMessage()));
        }
    }

    public function deleteProduct(int $id): void
    {
        if (!$this->authorizeRole(UserRole::ADMIN)) {
            return;
        }

        $this->productModel->delete($id);
        $this->sendResponse(200, ['message' => 'Product deleted successfully.']);
    }

    private function sendResponse(int $statusCode, mixed $data): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    private function authorizeRole(UserRole $requiredRole): bool
    {
        $token = $this->getBearerToken();
        if (!$token) {
            $this->sendResponse(401, 'Token is missing.');
            return false;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->key, JwtAlgorithm::HS256->value));
            if (UserRole::tryFrom($decoded->role) !== $requiredRole) {
                $this->sendResponse(403, 'Forbidden.');
                return false;
            }
        } catch (\Exception $e) {
            $this->sendResponse(401, 'Invalid token.');
            return false;
        }

        return true;
    }

    private function getBearerToken(): ?string
    {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $authorization = $headers['Authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? null);

        if ($authorization && preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function getJsonPayload(): array
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        return is_array($payload) ? $payload : [];
    }

    private function getJwtKey(): string
    {
        $config = include __DIR__ . '/../config/jwt.php';
        return $config['key'];
    }
}
