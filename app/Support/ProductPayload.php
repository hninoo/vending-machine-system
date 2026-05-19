<?php
declare(strict_types=1);

namespace App\Support;

use App\Enums\ProductBadge;
use InvalidArgumentException;

final class ProductPayload
{
    private const MAX_NAME_LENGTH = 255;
    private const MAX_INT = 2147483647;
    private const MAX_DECIMAL_10_3 = 9999999.999;

    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $quantity,
        public readonly ProductBadge $badge,
        public readonly ?float $oldPrice,
    ) {}

    public static function fromArray(array $input): self
    {
        $name = trim((string) ($input['name'] ?? ''));
        $price = $input['price'] ?? null;
        $quantity = $input['quantity_available'] ?? ($input['quantity'] ?? null);
        $badge = ProductBadge::tryFrom((string) ($input['product_badge'] ?? $input['badge'] ?? ProductBadge::NONE->value));
        $oldPrice = $input['old_price'] ?? null;

        if ($name === '') {
            throw new InvalidArgumentException('Product name is required.');
        }

        if (self::length($name) > self::MAX_NAME_LENGTH) {
            throw new InvalidArgumentException('Product name must be 255 characters or fewer.');
        }

        $price = self::decimal($price, 'Price');
        $quantity = self::integer($quantity, 'Quantity', 0);

        if ($badge === null) {
            throw new InvalidArgumentException('Invalid product badge.');
        }

        return new self(
            name: $name,
            price: $price,
            quantity: $quantity,
            badge: $badge,
            oldPrice: self::oldPrice($oldPrice, $price, $badge),
        );
    }

    private static function oldPrice(mixed $oldPrice, float $price, ProductBadge $badge): ?float
    {
        if ($badge !== ProductBadge::SALE) {
            return null;
        }

        if ($oldPrice === null || $oldPrice === '') {
            throw new InvalidArgumentException('Discount compare price is required for sale products.');
        }

        $oldPrice = self::decimal($oldPrice, 'Discount compare price');

        if ($oldPrice <= $price) {
            throw new InvalidArgumentException('Discount compare price must be greater than the selling price.');
        }

        return $oldPrice;
    }

    private static function decimal(mixed $value, string $field): float
    {
        if (! is_int($value) && ! is_float($value) && ! is_string($value)) {
            throw new InvalidArgumentException(sprintf('%s must be a positive number with up to 3 decimal places.', $field));
        }

        $stringValue = trim((string) $value);

        if (! preg_match('/^\d+(?:\.\d{1,3})?$/', $stringValue)) {
            throw new InvalidArgumentException(sprintf('%s must be a positive number with up to 3 decimal places.', $field));
        }

        $decimal = (float) $stringValue;
        if ($decimal <= 0 || $decimal > self::MAX_DECIMAL_10_3) {
            throw new InvalidArgumentException(sprintf('%s must be between 0.001 and 9999999.999.', $field));
        }

        return $decimal;
    }

    private static function integer(mixed $value, string $field, int $min): int
    {
        if (is_bool($value)) {
            throw new InvalidArgumentException(sprintf('%s must be an integer between %d and %d.', $field, $min, self::MAX_INT));
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $min, 'max_range' => self::MAX_INT],
        ]);

        if ($integer === false) {
            throw new InvalidArgumentException(sprintf('%s must be an integer between %d and %d.', $field, $min, self::MAX_INT));
        }

        return $integer;
    }

    private static function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
