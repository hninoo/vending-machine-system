<?php
declare(strict_types=1);

namespace Web\Controllers;

class PublicController
{
    public function index(): void
    {
        require __DIR__ . '/../views/public/index.php';
    }

    public function shop(): void
    {
        header('Location: /', true, 301);
        exit;
    }
}
