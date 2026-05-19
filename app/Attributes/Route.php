<?php
declare(strict_types=1);

namespace App\Attributes;

use App\Enums\HttpMethod;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public readonly HttpMethod $method,
        public readonly string $path,
    ) {
    }
}
