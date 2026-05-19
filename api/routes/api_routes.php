<?php
declare(strict_types=1);

use App\Enums\HttpMethod;

$router->addRoute(HttpMethod::POST, '/api/v1/login', 'Api\Controllers\AuthController@login');
$router->addRoute(HttpMethod::POST, '/api/v1/logout', 'Api\Controllers\AuthController@logout');
$router->addRoute(HttpMethod::GET, '/api/v1/products', 'Api\Controllers\ProductsController@getProducts');
$router->addRoute(HttpMethod::GET, '/api/v1/products/{id}', 'Api\Controllers\ProductsController@getProduct');
$router->addRoute(HttpMethod::POST, '/api/v1/products', 'Api\Controllers\ProductsController@createProduct');
$router->addRoute(HttpMethod::PUT, '/api/v1/products/{id}', 'Api\Controllers\ProductsController@updateProduct');
$router->addRoute(HttpMethod::DELETE, '/api/v1/products/{id}', 'Api\Controllers\ProductsController@deleteProduct');
$router->addRoute(HttpMethod::POST, '/api/v1/transactions', 'Api\Controllers\TransactionsController@createTransaction');
$router->addRoute(HttpMethod::POST, '/api/v1/cart/checkout', 'Api\Controllers\CartController@checkout');
$router->addRoute(HttpMethod::POST, '/api/v1/customer/register', 'Api\Controllers\CustomerAuthController@register');
$router->addRoute(HttpMethod::POST, '/api/v1/customer/login', 'Api\Controllers\CustomerAuthController@login');
$router->addRoute(HttpMethod::POST, '/api/v1/customer/logout', 'Api\Controllers\CustomerAuthController@logout');
$router->addRoute(HttpMethod::GET, '/api/v1/customer/me', 'Api\Controllers\CustomerAuthController@me');
