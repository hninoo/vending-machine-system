<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Enums\ProductBadge;
use InvalidArgumentException;
use PDO;

class Product
{
    private const MAX_NAME_LENGTH = 255;
    private const MAX_INT = 2147483647;
    private const MAX_DECIMAL_10_3 = 9999999.999;

    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? (new Database())->getConnection();
    }

    public function all(int $limit = 0, int $offset = 0, string $sortBy = 'id', string $direction = 'ASC'): array
    {
        $sortBy = $this->normalizeSortColumn($sortBy);
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql = sprintf(
            "SELECT id, name, price, quantity_available, product_badge, old_price
             FROM products
             ORDER BY %s %s",
            $sortBy,
            $direction,
        );

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
        $statement = $this->connection->query("SELECT COUNT(*) as total FROM products");
        return (int) $statement->fetch()['total'];
    }

    public function create(string $name, float $price, int $quantity, string $badge = 'none', ?float $oldPrice = null): string
    {
        $badge = $this->normalizeBadge($badge);
        $oldPrice = $this->normalizeOldPrice($badge, $oldPrice, $price);
        $this->validateWriteData($name, $price, $quantity, $oldPrice);

        $statement = $this->connection->prepare(
            "INSERT INTO products (name, price, quantity_available, product_badge, old_price)
             VALUES (:name, :price, :quantity, :badge, :old_price)"
        );
        $statement->execute([
            ':name' => $name,
            ':price' => $price,
            ':quantity' => $quantity,
            ':badge' => $badge,
            ':old_price' => $oldPrice,
        ]);

        return $this->connection->lastInsertId();
    }

    public function get(int $id): array|false
    {
        $statement = $this->connection->prepare(
            "SELECT id, name, price, quantity_available, product_badge, old_price
             FROM products
             WHERE id = :id"
        );
        $statement->execute([':id' => $id]);
        return $statement->fetch();
    }

    public function decrementStock(int $id, int $quantity): bool
    {
        $statement = $this->connection->prepare(
            "UPDATE products
             SET quantity_available = quantity_available - :decrement_quantity
             WHERE id = :id AND quantity_available >= :required_quantity"
        );
        $statement->execute([
            ':decrement_quantity' => $quantity,
            ':required_quantity' => $quantity,
            ':id' => $id,
        ]);

        return $statement->rowCount() === 1;
    }

    public function update(
        int $id,
        string $name,
        float $price,
        int $quantity,
        string $badge = 'none',
        ?float $oldPrice = null,
    ): void {
        $badge = $this->normalizeBadge($badge);
        $oldPrice = $this->normalizeOldPrice($badge, $oldPrice, $price);
        $this->validateWriteData($name, $price, $quantity, $oldPrice);

        $statement = $this->connection->prepare(
            "UPDATE products
             SET name = :name, price = :price, quantity_available = :quantity, product_badge = :badge, old_price = :old_price
             WHERE id = :id"
        );
        $statement->execute([
            ':name' => $name,
            ':price' => $price,
            ':quantity' => $quantity,
            ':badge' => $badge,
            ':old_price' => $oldPrice,
            ':id' => $id,
        ]);
    }

    public function updateDetails(int $id, string $name, float $price, int $quantity, string $badge = 'none', ?float $oldPrice = null): void
    {
        $this->update($id, $name, $price, $quantity, $badge, $oldPrice);
    }

    public function delete(int $id): void
    {
        $statement = $this->connection->prepare("DELETE FROM products WHERE id = :id");
        $statement->execute([':id' => $id]);
    }

    private function normalizeSortColumn(string $sortBy): string
    {
        $allowedColumns = ['id', 'name', 'price', 'quantity_available', 'product_badge', 'old_price'];

        return in_array($sortBy, $allowedColumns, true) ? $sortBy : 'id';
    }

    private function normalizeBadge(string $badge): string
    {
        return ProductBadge::tryFrom($badge)?->value ?? ProductBadge::NONE->value;
    }

    private function normalizeOldPrice(string $badge, ?float $oldPrice, float $price): ?float
    {
        if ($badge !== ProductBadge::SALE->value) {
            return null;
        }

        if ($oldPrice === null) {
            throw new InvalidArgumentException('Discount compare price is required for sale products.');
        }

        if ($oldPrice <= $price) {
            throw new InvalidArgumentException('Discount compare price must be greater than the selling price.');
        }

        return $oldPrice;
    }

    private function validateWriteData(string $name, float $price, int $quantity, ?float $oldPrice): void
    {
        $nameLength = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
        if ($nameLength === 0 || $nameLength > self::MAX_NAME_LENGTH) {
            throw new InvalidArgumentException('Product name must be between 1 and 255 characters.');
        }

        $this->validateDecimal($price, 'Price');

        if ($quantity < 0 || $quantity > self::MAX_INT) {
            throw new InvalidArgumentException(sprintf('Quantity must be an integer between 0 and %d.', self::MAX_INT));
        }

        if ($oldPrice !== null) {
            $this->validateDecimal($oldPrice, 'Discount compare price');
            if ($oldPrice <= $price) {
                throw new InvalidArgumentException('Discount compare price must be greater than the selling price.');
            }
        }
    }

    private function validateDecimal(float $value, string $field): void
    {
        if ($value <= 0 || $value > self::MAX_DECIMAL_10_3 || abs(round($value, 3) - $value) > 0.0000001) {
            throw new InvalidArgumentException(sprintf('%s must be between 0.001 and 9999999.999 with up to 3 decimal places.', $field));
        }
    }
}
