# Stockroom API (Laravel)

REST API for the Product Inventory & Order Management service. Handles product CRUD and
order placement with atomic, race-safe stock control.

> Project-wide setup, the database-choice rationale, and the concurrency write-up live in
> the [root README](../README.md). This file is the day-to-day reference for working in the
> backend.

## Requirements

- PHP 8.3+
- Composer 2
- MySQL 8 (SQLite is used only for the test suite)

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
# create the `stockroom` database, then:
php artisan migrate --seed
php artisan serve            # http://127.0.0.1:8000
```

## Configuration

| Variable               | Purpose                                                        |
| ---------------------- | -------------------------------------------------------------- |
| `DB_*`                 | MySQL connection                                               |
| `PRODUCTS_PER_PAGE`    | Default product page size (overridable per request, max 50)    |
| `CORS_ALLOWED_ORIGINS` | Comma-separated origins allowed to call the API (the SPA)      |

## Architecture

Concerns are kept separate; nothing important lives in a controller.

```
routes/api.php                route definitions (public login, token-guarded data routes)
app/Http/Controllers          HTTP only — delegate to services / model queries
app/Http/Controllers/Auth...  AuthController — login / logout / current user
app/Http/Requests             FormRequest validation (Login, Store/Update product, Store order)
app/Http/Resources            response serialization (consistent { data: ... } shape)
app/Services/OrderService     the order/stock transaction (validate -> lock -> decrement)
app/Exceptions                InsufficientStockException (renders 409 with shortfalls)
app/Models                    User, Product, Order, OrderItem + relationships and casts
```

## Authentication

Token-based auth with **Laravel Sanctum**. `POST /login` checks credentials with
`Hash::check` and returns a personal access token; protected routes use the `auth:sanctum`
middleware and expect `Authorization: Bearer <token>`. Unauthenticated requests to `/api/*`
return `401` JSON. The seeder creates a demo account: **`admin@stockroom.test` / `password`**.

## API reference

Base path `/api`. Responses are JSON wrapped in `data`. Every route except `POST /login`
requires a bearer token.

| Method   | Endpoint               | Auth  | Description                                                      |
| -------- | ---------------------- | :---: | --------------------------------------------------------------- |
| `POST`   | `/login`               |   -   | `{ email, password }` → `{ token, user }`                       |
| `GET`    | `/user`                | token | The authenticated user                                          |
| `POST`   | `/logout`              | token | Revoke the current token                                        |
| `GET`    | `/products`            | token | Paginated list. Query: `search`, `category`, `page`, `per_page` |
| `GET`    | `/products/categories` | token | Distinct category names (for filters)                           |
| `GET`    | `/products/{id}`       | token | Single product                                                  |
| `POST`   | `/products`            | token | Create a product                                                |
| `PUT`    | `/products/{id}`       | token | Update a product                                                |
| `DELETE` | `/products/{id}`       | token | Delete (409 if it belongs to an order)                          |
| `GET`    | `/orders`              | token | Orders with line items and totals                               |
| `GET`    | `/orders/{id}`         | token | Single order                                                    |
| `POST`   | `/orders`              | token | Place an order (validates + decrements stock)                   |

Paginated list responses include Laravel's `links` and `meta` (`current_page`,
`last_page`, `per_page`, `total`).

### Example

```bash
# sign in and capture the token
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@stockroom.test","password":"password"}' | jq -r .data.token)

# call a protected endpoint
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"items":[{"product_id":1,"quantity":2}]}'
```

### Status codes

| Code  | Meaning                                                      |
| ----- | ------------------------------------------------------------ |
| `201` | Created                                                      |
| `204` | Deleted                                                      |
| `401` | Missing/invalid bearer token                                |
| `409` | Insufficient stock - `{ message, errors: { items: [...] } }` |
| `422` | Validation error - `{ message, errors: { field: [...] } }`  |

## Concurrency

`OrderService::place()` runs inside `DB::transaction()` and selects the ordered products
with `lockForUpdate()` (`SELECT ... FOR UPDATE`, ordered by id to avoid deadlocks). Stock is
validated under the lock and decremented before commit, so two orders for the last unit
can never both succeed.

## Tests

```bash
php artisan test
```

Feature tests cover login/logout, the auth guard, product CRUD, filtering, pagination, and the full
order/stock path (decrement, totals, insufficient-stock `409`, last-unit contention,
duplicate-line merging). A unit test covers the insufficient-stock exception contract.

The suite runs on in-memory SQLite by default; CI runs it against MySQL so the row lock is
exercised on the real engine.
