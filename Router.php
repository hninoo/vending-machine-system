<?php
declare(strict_types=1);

use App\Enums\HttpMethod;
use App\Attributes\Route;

final class Router
{
    private array $routes = [];

    public function addRoute(HttpMethod|string $method, string $uri, string $handler): void
    {
        $methodValue = $method instanceof HttpMethod ? $method->value : $method;
        $this->routes[$methodValue][$uri] = $handler;
    }

    public function addAttributeRoutes(string $controller): void
    {
        $reflection = new \ReflectionClass($controller);

        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes(Route::class) as $attribute) {
                $route = $attribute->newInstance();
                $this->addRoute($route->method, $route->path, $controller . '@' . $method->getName());
            }
        }
    }

    public function dispatch(HttpMethod|string $method, string $uri): mixed
    {
        $methodValue = $method instanceof HttpMethod ? $method->value : $method;
        $lookupMethod = $methodValue === 'HEAD' ? HttpMethod::GET->value : $methodValue;
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes[$lookupMethod] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route);
            $pattern = str_replace('/', '\/', $pattern);
            $pattern = '/^' . $pattern . '$/';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $matches = array_map(
                    static fn (string $value): int|string => ctype_digit($value) ? (int) $value : $value,
                    $matches,
                );
                list($controller, $action) = explode('@', $handler);
                $controllerInstance = new $controller();
                
                if (method_exists($controllerInstance, $action)) {
                    if ($methodValue === 'HEAD') {
                        ob_start();
                        $result = $controllerInstance->$action(...$matches);
                        ob_end_clean();
                        return $result;
                    }

                    return $controllerInstance->$action(...$matches);
                }
            }
        }

        // Handle 404 - Not Found
        http_response_code(404);
        echo "404 - Not Found";
    }
}
