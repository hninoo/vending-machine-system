<?php
declare(strict_types=1);

use App\Enums\HttpMethod;

$router->addRoute(HttpMethod::GET, '/', 'Web\Controllers\PublicController@index');
$router->addRoute(HttpMethod::GET, '/shop', 'Web\Controllers\PublicController@shop');
$router->addRoute(HttpMethod::GET, '/admin', 'Web\Controllers\AdminController@login');
$router->addRoute(HttpMethod::GET, '/admin/login', 'Web\Controllers\AdminController@login');
$router->addRoute(HttpMethod::POST, '/admin/login', 'Web\Controllers\AdminController@login');
$router->addRoute(HttpMethod::POST, '/admin/logout', 'Web\Controllers\AdminController@logout');
$router->addRoute(HttpMethod::GET, '/admin/dashboard', 'Web\Controllers\AdminController@dashboard');

$router->addRoute(HttpMethod::GET, '/products', 'Web\Controllers\ProductsController@index');
$router->addRoute(HttpMethod::GET, '/products/create', 'Web\Controllers\ProductsController@create');
$router->addRoute(HttpMethod::POST, '/products/create', 'Web\Controllers\ProductsController@create');
$router->addRoute(HttpMethod::GET, '/products/{id}/edit', 'Web\Controllers\ProductsController@edit');
$router->addRoute(HttpMethod::POST, '/products/{id}/update', 'Web\Controllers\ProductsController@update');
$router->addRoute(HttpMethod::POST, '/products/{id}/delete', 'Web\Controllers\ProductsController@delete');
$router->addRoute(HttpMethod::GET, '/products/{id}/purchase', 'Web\Controllers\ProductsController@showPurchase');
$router->addAttributeRoutes(Web\Controllers\ProductsController::class);
$router->addRoute(HttpMethod::GET, '/users', 'Web\Controllers\UsersController@index');
$router->addRoute(HttpMethod::GET, '/users/create', 'Web\Controllers\UsersController@create');
$router->addRoute(HttpMethod::POST, '/users/create', 'Web\Controllers\UsersController@create');
$router->addRoute(HttpMethod::GET, '/users/{id}/edit', 'Web\Controllers\UsersController@edit');
$router->addRoute(HttpMethod::POST, '/users/{id}/update', 'Web\Controllers\UsersController@edit');
$router->addRoute(HttpMethod::POST, '/users/{id}/delete', 'Web\Controllers\UsersController@delete');

$router->addRoute(HttpMethod::GET, '/transactions', 'Web\Controllers\TransactionsController@index');
$router->addRoute(HttpMethod::GET, '/transactions/create', 'Web\Controllers\TransactionsController@create');
$router->addRoute(HttpMethod::POST, '/transactions/create', 'Web\Controllers\TransactionsController@create');
$router->addRoute(HttpMethod::GET, '/transactions/{id}/edit', 'Web\Controllers\TransactionsController@edit');
$router->addRoute(HttpMethod::POST, '/transactions/{id}/update', 'Web\Controllers\TransactionsController@update');
$router->addRoute(HttpMethod::POST, '/transactions/{id}/delete', 'Web\Controllers\TransactionsController@delete');
