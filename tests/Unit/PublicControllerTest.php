<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Web\Controllers\PublicController;

final class PublicControllerTest extends TestCase
{
    public function testIndexRendersStorefrontApp(): void
    {
        ob_start();
        (new PublicController())->index();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertStringContainsString('id="public-shop-app"', $output);
        $this->assertStringContainsString('<title>Vending Machine</title>', $output);
    }

    public function testStorefrontUsesRootUrlAsCanonicalLinkTarget(): void
    {
        ob_start();
        (new PublicController())->index();
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertStringContainsString('class="visible-logo" href="/"', $output);
        $this->assertStringNotContainsString('href="/shop"', $output);
    }
}
