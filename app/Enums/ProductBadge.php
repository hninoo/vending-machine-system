<?php
declare(strict_types=1);

namespace App\Enums;

enum ProductBadge: string
{
    case NONE = 'none';
    case NEW = 'new';
    case SALE = 'sale';
}
