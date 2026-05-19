<?php
declare(strict_types=1);

namespace Api\Controllers;

use App\Config\Database;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class CartController
{
    private const PUBLIC_USERNAME = 'public-customer';
    private const CUSTOMER_SESSION_KEY = 'customer_user_id';

    private Product $productModel;
    private Transaction $transactionModel;
    private User $userModel;
    private PDO $connection;

    public function __construct(
        ?Product $productModel = null,
        ?Transaction $transactionModel = null,
        ?User $userModel = null,
        ?PDO $connection = null,
    ) {
        $this->connection = $connection ?? (new Database())->getConnection();
        $this->productModel = $productModel ?? new Product($this->connection);
        $this->transactionModel = $transactionModel ?? new Transaction($this->connection);
        $this->userModel = $userModel ?? new User($this->connection);
    }

    public function checkout(): void
    {
        $payload = $this->jsonPayload();
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];

        if (empty($items)) {
            $this->respond(422, ['ok' => false, 'message' => 'Cart is empty.']);
            return;
        }

        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                $this->respond(422, ['ok' => false, 'message' => 'Each cart item must be an object.']);
                return;
            }

            try {
                $productId = $this->positiveInt($item['product_id'] ?? $item['id'] ?? null, 'Product ID');
                $quantity = $this->positiveInt($item['quantity'] ?? null, 'Quantity');
            } catch (InvalidArgumentException $e) {
                $this->respond(422, ['ok' => false, 'message' => $e->getMessage()]);
                return;
            }

            if ($productId <= 0 || $quantity <= 0) {
                $this->respond(422, ['ok' => false, 'message' => 'Each item needs a valid product_id and quantity.']);
                return;
            }

            $normalized[] = ['product_id' => $productId, 'quantity' => $quantity];
        }

        try {
            $this->connection->beginTransaction();
            $userId = $this->resolveCheckoutUserId();
            $receipt = [];
            $total = 0.0;

            foreach ($normalized as $line) {
                $product = $this->productModel->get($line['product_id']);
                if (!$product) {
                    throw new RuntimeException(sprintf('Product %d not found.', $line['product_id']));
                }

                $available = (int) $product['quantity_available'];
                if ($line['quantity'] > $available) {
                    throw new RuntimeException(sprintf('Not enough stock for %s.', (string) $product['name']));
                }

                $lineTotal = (float) $product['price'] * $line['quantity'];
                if (!$this->productModel->decrementStock((int) $product['id'], $line['quantity'])) {
                    throw new RuntimeException(sprintf('Not enough stock for %s.', (string) $product['name']));
                }
                $transactionId = (int) $this->transactionModel->create($userId, (int) $product['id'], $line['quantity'], $lineTotal);

                $receipt[] = [
                    'transaction_id' => $transactionId,
                    'product_id' => (int) $product['id'],
                    'name' => (string) $product['name'],
                    'quantity' => $line['quantity'],
                    'unit_price' => (float) $product['price'],
                    'line_total' => $lineTotal,
                ];
                $total += $lineTotal;
            }

            $this->connection->commit();

            $this->respond(201, [
                'ok' => true,
                'message' => sprintf('Purchased %d item(s) for $%s.', array_sum(array_column($receipt, 'quantity')), number_format($total, 2)),
                'total' => $total,
                'transactions' => $receipt,
            ]);
        } catch (RuntimeException $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            $this->respond(422, ['ok' => false, 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            $this->respond(500, ['ok' => false, 'message' => 'Checkout failed.']);
        }
    }

    private function resolveCheckoutUserId(): int
    {
        $sessionId = $_SESSION[self::CUSTOMER_SESSION_KEY] ?? null;
        if (is_int($sessionId) || (is_string($sessionId) && ctype_digit($sessionId))) {
            $user = $this->userModel->get((int) $sessionId);
            if ($user) {
                return (int) $user['id'];
            }
            unset($_SESSION[self::CUSTOMER_SESSION_KEY]);
        }

        $user = $this->userModel->findByUsername(self::PUBLIC_USERNAME);
        if ($user) {
            return (int) $user['id'];
        }
        return $this->userModel->register(self::PUBLIC_USERNAME, bin2hex(random_bytes(16)), UserRole::USER);
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
