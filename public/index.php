<?php
declare(strict_types=1);

session_start();

require '../vendor/autoload.php';
require '../Router.php';

$router = new Router();

// Load Web routes
require '../web/routes/web_routes.php';

// Load API routes
require '../api/routes/api_routes.php';

// Dispatch request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
