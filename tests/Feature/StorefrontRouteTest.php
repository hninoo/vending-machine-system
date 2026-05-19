<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\HttpMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

require_once dirname(__DIR__, 2) . '/Router.php';

final class StorefrontRouteTest extends TestCase
{
    public function testRootRouteRendersThePublicStorefront(): void
    {
        $router = new \Router();
        require dirname(__DIR__, 2) . '/web/routes/web_routes.php';

        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $router->dispatch(HttpMethod::GET, '/');
        $output = ob_get_clean();

        $this->assertIsString($output);
        $this->assertSame(200, http_response_code() ?: 200);
        $this->assertStringContainsString('id="public-shop-app"', $output);
        $this->assertStringContainsString('<title>Vending Machine</title>', $output);
    }

    public function testShopRouteIsRegisteredAsRedirectAction(): void
    {
        $router = new \Router();
        require dirname(__DIR__, 2) . '/web/routes/web_routes.php';

        $routes = $this->registeredRoutes($router);

        $this->assertSame('Web\Controllers\PublicController@index', $routes['GET']['/']);
        $this->assertSame('Web\Controllers\PublicController@shop', $routes['GET']['/shop']);
    }

    public function testHeadRequestUsesRegisteredGetRouteWithoutBody(): void
    {
        $router = new \Router();
        require dirname(__DIR__, 2) . '/web/routes/web_routes.php';

        ob_start();
        $router->dispatch('HEAD', '/');
        $output = ob_get_clean();

        $this->assertSame('', $output);
        $this->assertSame(200, http_response_code() ?: 200);
    }

    /**
     * @return array<string,array<string,string>>
     */
    private function registeredRoutes(\Router $router): array
    {
        $property = (new ReflectionClass($router))->getProperty('routes');

        return $property->getValue($router);
    }
}
