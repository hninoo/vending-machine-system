<?php
declare(strict_types=1);

namespace App\Enums;

enum JwtAlgorithm: string
{
    case HS256 = 'HS256';
}
