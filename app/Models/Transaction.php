<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class Transaction
{
    private const MAX_INT = 2147483647;
    private const MAX_DECIMAL_10_3 = 9999999.999;

    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? (new Database())->getConnection();
    }

    public function all(int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT transactions.id,
                       transactions.user_id,
                       transactions.product_id,
                       transactions.quantity,
                       transactions.total_price,
                       transactions.transaction_date,
                       users.username,
                       products.name AS product_name
                FROM transactions
                JOIN users ON transactions.user_id = users.id
                JOIN products ON transactions.product_id = products.id
                ORDER BY transactions.id DESC";
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
        $statement = $this->connection->query("SELECT COUNT(*) as total FROM transactions");
        return (int) $statement->fetch()['total'];
    }

    public function create(int $userId, int $productId, int $quantity, float $totalPrice): string
    {
        $this->validateWriteData($userId, $productId, $quantity, $totalPrice);

        $statement = $this->connection->prepare("INSERT INTO transactions (user_id, product_id, quantity, total_price) VALUES (:user_id, :product_id, :quantity, :total_price)");
        $statement->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':quantity' => $quantity,
            ':total_price' => $totalPrice,
        ]);

        return $this->connection->lastInsertId();
    }

    public function get(int $id): array|false
    {
        $statement = $this->connection->prepare(
            "SELECT id, user_id, product_id, quantity, total_price, transaction_date
             FROM transactions
             WHERE id = :id"
        );
        $statement->execute([':id' => $id]);
        return $statement->fetch();
    }

    public function update(int $id, int $userId, int $productId, int $quantity, float $totalPrice): void
    {
        $this->validateId($id, 'Transaction ID');
        $this->validateWriteData($userId, $productId, $quantity, $totalPrice);

        $statement = $this->connection->prepare("UPDATE transactions SET user_id = :user_id, product_id = :product_id, quantity = :quantity, total_price = :total_price WHERE id = :id");
        $statement->execute([':user_id' => $userId, ':product_id' => $productId, ':quantity' => $quantity, ':total_price' => $totalPrice, ':id' => $id]);
    }

    public function delete(int $id): void
    {
        $statement = $this->connection->prepare("DELETE FROM transactions WHERE id = :id");
        $statement->execute([':id' => $id]);
    }

    public function calculatePrice(int $productId, int $quantity): float
    {
        $statement = $this->connection->prepare("SELECT price FROM products WHERE id = :product_id");
        $statement->execute([':product_id' => $productId]);
        $product = $statement->fetch();

        if (!$product) {
            throw new RuntimeException("Product not found.");
        }

        return (float) $product['price'] * $quantity;
    }

    private function validateWriteData(int $userId, int $productId, int $quantity, float $totalPrice): void
    {
        $this->validateId($userId, 'User ID');
        $this->validateId($productId, 'Product ID');
        $this->validateId($quantity, 'Quantity');

        if ($totalPrice <= 0 || $totalPrice > self::MAX_DECIMAL_10_3 || abs(round($totalPrice, 3) - $totalPrice) > 0.0000001) {
            throw new InvalidArgumentException('Total price must be between 0.001 and 9999999.999 with up to 3 decimal places.');
        }
    }

    private function validateId(int $value, string $field): void
    {
        if ($value <= 0 || $value > self::MAX_INT) {
            throw new InvalidArgumentException(sprintf('%s must be an integer between 1 and %d.', $field, self::MAX_INT));
        }
    }
}
