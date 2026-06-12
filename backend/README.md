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
| `API_KEY`              | Secret required in the `X-API-Key` header on write endpoints   |
| `PRODUCTS_PER_PAGE`    | Default product page size (overridable per request, max 50)    |
| `CORS_ALLOWED_ORIGINS` | Comma-separated origins allowed to call the API (the SPA)      |

## Architecture

Concerns are kept separate; nothing important lives in a controller.

```
routes/api.php                route definitions (public reads, key-guarded writes)
app/Http/Controllers          HTTP only — delegate to services / model queries
app/Http/Requests             FormRequest validation (Store/Update product, Store order)
app/Http/Resources            response serialization (consistent { data: ... } shape)
app/Http/Middleware           EnsureApiKey — protects write endpoints
app/Services/OrderService     the order/stock transaction (validate -> lock -> decrement)
app/Exceptions                InsufficientStockException (renders 409 with shortfalls)
app/Models                    Product, Order, OrderItem + relationships and casts
```

## Authentication

Read endpoints are public. **Write endpoints require an API key** sent as a header:

```
X-API-Key: <your API_KEY>
```

Missing or wrong keys return `401`. An API key is the lightest credible guard for an
internal operator tool; a JWT/session layer would be the next step for multi-user auth.

## API reference

Base path `/api`. Responses are JSON wrapped in `data`.

| Method   | Endpoint               | Auth | Description                                                      |
| -------- | ---------------------- | :--: | ---------------------------------------------------------------- |
| `GET`    | `/products`            |  -   | Paginated list. Query: `search`, `category`, `page`, `per_page` |
| `GET`    | `/products/categories` |  -   | Distinct category names (for filters)                           |
| `GET`    | `/products/{id}`       |  -   | Single product                                                  |
| `POST`   | `/products`            | key  | Create a product                                                |
| `PUT`    | `/products/{id}`       | key  | Update a product                                                |
| `DELETE` | `/products/{id}`       | key  | Delete (409 if it belongs to an order)                          |
| `GET`    | `/orders`              |  -   | Orders with line items and totals                               |
| `GET`    | `/orders/{id}`         |  -   | Single order                                                    |
| `POST`   | `/orders`              | key  | Place an order (validates + decrements stock)                   |

Paginated list responses include Laravel's `links` and `meta` (`current_page`,
`last_page`, `per_page`, `total`).

### Example

```bash
# public read
curl "http://127.0.0.1:8000/api/products?per_page=8&category=Kitchen"

# guarded write
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "X-API-Key: local-development-key" \
  -d '{"items":[{"product_id":1,"quantity":2}]}'
```

### Status codes

| Code  | Meaning                                                      |
| ----- | ------------------------------------------------------------ |
| `201` | Created                                                      |
| `204` | Deleted                                                      |
| `401` | Missing/invalid API key                                     |
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

Feature tests cover product CRUD, filtering, pagination, the API-key guard, and the full
order/stock path (decrement, totals, insufficient-stock `409`, last-unit contention,
duplicate-line merging). A unit test covers the insufficient-stock exception contract.

The suite runs on in-memory SQLite by default; CI runs it against MySQL so the row lock is
exercised on the real engine.
