<?php
declare(strict_types=1);

namespace Web\Traits;

use App\Enums\UserRole;

trait AdminCheckTrait
{
    public function checkAdmin(): bool
    {
        if (isset($_SESSION['role']) && $_SESSION['role'] === UserRole::ADMIN->value) {
            return true;
        }

        header('Location: /');
        exit();
    }
}
