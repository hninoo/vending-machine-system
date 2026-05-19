# Vending Machine Application

A PHP vending machine system built with a custom MVC structure, PDO/MySQL database access, admin management screens, a public storefront, and REST API endpoints.

## Core Features

- Product management: admin CRUD, pagination, and sorting.
- Inventory tracking: stock is stored in `quantity_available` and decremented on purchase.
- Purchase transactions: every checkout creates a transaction record.
- Authentication: admin session login with password hashing and role checks.
- API: REST endpoints for products, transactions, cart checkout, and customer authentication.

## Product Fields

Required product fields from the challenge:

- `id` - `INT`
- `name` - `VARCHAR(255)`
- `price` - `DECIMAL(10,3)`
- `quantity_available` - `INT`

Additional storefront fields:

- `product_badge` - `none`, `new`, or `sale`
- `old_price` - nullable compare price, required only for sale products

## Tech Stack

- PHP 8.5
- MySQL
- PDO
- PHPUnit
- Docker Compose
- Nginx

## Quick Start

```bash
docker compose up --build -d
```

Open:

```text
http://localhost:8000
```

Reset and reseed the database:

```bash
docker compose down -v
docker compose up --build -d
```

## Admin Login

```text
Username: admin
Password: admin123
```

Admin URL:

```text
http://localhost:8000/admin/login
```

## Database

The schema and seed data are in:

```text
database.sql
```

Main tables:

- `products`
- `users`
- `transactions`

## API

Main API routes:

- `GET /api/v1/products`
- `GET /api/v1/products/{id}`
- `POST /api/v1/products`
- `PUT /api/v1/products/{id}`
- `DELETE /api/v1/products/{id}`
- `POST /api/v1/login`
- `POST /api/v1/transactions`
- `POST /api/v1/cart/checkout`
- `POST /api/v1/customer/register`
- `POST /api/v1/customer/login`
- `POST /api/v1/customer/logout`
- `GET /api/v1/customer/me`

Postman collection:

```text
vending-machine-system.postman_collection.json
```

## Tests

```bash
docker compose exec app composer test
```
