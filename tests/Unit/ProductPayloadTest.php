<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ProductBadge;
use App\Support\ProductPayload;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductPayloadTest extends TestCase
{
    public function test_payload_accepts_storefront_fields(): void
    {
        $payload = ProductPayload::fromArray([
            'name' => 'Pepsi',
            'price' => '6.885',
            'quantity' => '50',
            'product_badge' => 'sale',
            'old_price' => '7.385',
        ]);

        $this->assertSame('Pepsi', $payload->name);
        $this->assertSame(6.885, $payload->price);
        $this->assertSame(50, $payload->quantity);
        $this->assertSame(ProductBadge::SALE, $payload->badge);
        $this->assertSame(7.385, $payload->oldPrice);
    }

    public function test_compare_price_must_be_higher_than_selling_price(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductPayload::fromArray([
            'name' => 'Coke',
            'price' => '3.990',
            'quantity' => '10',
            'product_badge' => 'sale',
            'old_price' => '3.990',
        ]);
    }

    public function test_sale_products_require_compare_price(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductPayload::fromArray([
            'name' => 'Coke',
            'price' => '3.990',
            'quantity' => '10',
            'product_badge' => 'sale',
        ]);
    }

    public function test_non_sale_products_ignore_compare_price(): void
    {
        $payload = ProductPayload::fromArray([
            'name' => 'Coke',
            'price' => '3.990',
            'quantity' => '10',
            'product_badge' => 'none',
            'old_price' => '4.490',
        ]);

        $this->assertSame(ProductBadge::NONE, $payload->badge);
        $this->assertNull($payload->oldPrice);
    }
}
