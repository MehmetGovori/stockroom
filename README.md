# Stockroom — Product Inventory & Order Management

A deliberately scoped slice of an e-commerce backend: manage products and place orders
against live stock, with overselling prevented under concurrent requests.

- **Backend:** Laravel 13 (PHP 8.3) REST API
- **Frontend:** Vue 3 + TypeScript + Vite (Pinia, Vue Router, Axios)
- **Database:** MySQL 8
- **Runtime:** `docker compose up` from a clean checkout

---

## Quick start

### Option A — Docker (recommended)

```bash
docker compose up --build
```

This starts three services and seeds demo products automatically:

| Service  | URL                            | Notes                              |
| -------- | ------------------------------ | ---------------------------------- |
| Frontend | http://localhost:8081          | Vue SPA served by nginx            |
| Backend  | http://localhost:8000/api      | Laravel API                        |
| Database | localhost:3307                 | MySQL 8 (`stockroom`, root / no pw)|

The backend container waits for MySQL, runs migrations, and seeds the catalog before
serving. No further setup is required.

### Option B — Local (without Docker)

Requires PHP 8.3+, Composer, Node 20+, and a MySQL 8 instance.

```bash
# Backend
cd backend
cp .env.example .env
composer install
php artisan key:generate
# create the database, then:
php artisan migrate --seed
php artisan serve            # http://127.0.0.1:8000

# Frontend (second terminal)
cd frontend
npm install
npm run dev                  # http://localhost:5173
```

The frontend reads the API base URL from `VITE_API_URL` (defaults to
`http://localhost:8000/api`).

---

## API

Base path: `/api`. All responses are JSON wrapped in a `data` key.

### Products

| Method   | Endpoint          | Description                                   |
| -------- | ----------------- | --------------------------------------------- |
| `GET`    | `/products`       | List products (`?search=` and `?category=`)   |
| `POST`   | `/products`       | Create a product                              |
| `GET`    | `/products/{id}`  | Show a product                                |
| `PUT`    | `/products/{id}`  | Update a product                              |
| `DELETE` | `/products/{id}`  | Delete a product (blocked if it has orders)   |

### Orders

| Method | Endpoint        | Description                                    |
| ------ | --------------- | ---------------------------------------------- |
| `GET`  | `/orders`       | List orders with line items and totals         |
| `POST` | `/orders`       | Place an order, validating and decrementing stock |
| `GET`  | `/orders/{id}`  | Show an order                                  |

**Place an order**

```http
POST /api/orders
Content-Type: application/json

{
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 6, "quantity": 3 }
  ]
}
```

### Status codes & error shapes

| Code  | Meaning                                                            |
| ----- | ----------------------------------------------------------------- |
| `201` | Resource created                                                  |
| `204` | Deleted                                                           |
| `409` | Insufficient stock (see shape below)                              |
| `422` | Validation error (`{ "message", "errors": { field: [...] } }`)    |

Insufficient-stock response:

```json
{
  "message": "One or more items are out of stock.",
  "errors": {
    "items": [
      { "product_id": 7, "requested": 5, "available": 3 }
    ]
  }
}
```

---

## Architecture & key decisions

The backend keeps a clear separation of concerns:

```
routes/api.php        thin route definitions
Http/Controllers      HTTP only — delegate, never contain business rules
Http/Requests         validation lives in FormRequest classes
Http/Resources        consistent response serialization
Services/OrderService the order/stock transaction (the one place that matters)
Models                Eloquent models + relationships
```

- **Controllers stay thin.** `ProductController` is a standard resource controller.
  `OrderController::store` does one thing: hand the validated items to `OrderService`.
- **Validation is declarative** and isolated in `StoreProductRequest`,
  `UpdateProductRequest`, and `StoreOrderRequest`, so error responses are consistent
  and the controllers carry no validation noise.
- **Order totals are persisted, not recomputed on read.** Each `order_item` snapshots
  the product name and unit price at purchase time, so order history stays correct even
  if a product is later renamed, repriced, or deleted.
- **Money is computed with `bcmath`** (`bcadd`/`bcmul`) to avoid binary float drift on
  prices, then stored in `DECIMAL` columns.

### Trade-offs

- A dedicated `OrderService` is the only "pattern" introduced, because the order path is
  the one place with real invariants. Products are simple CRUD and don't need a service
  layer — adding one would be ceremony without payoff.
- The product list returns the full set (with optional `search`/`category` filtering)
  rather than paginating. For a catalog of this size that's the simpler, correct choice;
  pagination is noted under "what I'd do with more time".

---

## Database: why relational

MySQL was chosen because this domain is **inherently relational and integrity-critical**:

- Orders, order items, and products form clean one-to-many relationships enforced by
  foreign keys (`order_items.order_id`, `order_items.product_id`).
- The core operation — decrement stock only if available — needs **ACID transactions and
  row-level locking**, which relational engines provide natively. A document store would
  push this concurrency control into application code or optimistic retries.
- Stock and money are numeric invariants best expressed with `UNSIGNED INTEGER` and
  `DECIMAL` column constraints.

Indexes: `products.sku` is unique, `products.category` is indexed for filtering, and
`order_items` is indexed on `(order_id, product_id)`.

---

## Concurrency: how overselling is prevented

The deliberate constraint — *two orders for the last item must not both succeed* — is
handled with **pessimistic row locking inside a database transaction**
(`app/Services/OrderService.php`):

```php
DB::transaction(function () use ($quantities) {
    $products = Product::whereIn('id', array_keys($quantities))
        ->lockForUpdate()   // SELECT ... FOR UPDATE
        ->get()
        ->keyBy('id');

    // validate every line against locked stock, then decrement
});
```

1. The transaction opens and `lockForUpdate()` issues `SELECT … FOR UPDATE`, taking an
   exclusive lock on exactly the product rows in the order.
2. Stock is validated **while the rows are locked**. If any line exceeds available stock,
   the whole transaction rolls back and the API returns `409` — no partial decrements.
3. Otherwise each row is decremented, the order and its items are written, and the
   transaction commits, releasing the locks.

A second concurrent request for the same product **blocks** on the lock until the first
commits, then re-reads the now-decremented stock and is correctly rejected. This makes
the check-then-decrement sequence atomic, so the last unit can only be sold once.

**Alternative considered:** optimistic concurrency with a `version` column and a
compare-and-set on decrement. It avoids holding locks and scales better under high
contention, but needs client retry handling. For a small shop with low write contention,
pessimistic locking is simpler and impossible to get subtly wrong — so it earns its place
here.

---

## Tests

```bash
cd backend
php artisan test
```

Feature tests (`tests/Feature`) run against an in-memory SQLite database and cover the
order/stock logic that matters most:

- stock is decremented and the total is computed and persisted correctly
- an order exceeding stock is rejected (`409`) and **leaves stock untouched**
- two orders for the last unit cannot both succeed
- duplicate lines for the same product are merged
- empty/invalid orders are rejected (`422`)
- product CRUD, filtering, and unique-SKU validation

> Note: `lockForUpdate()` is a no-op on SQLite, so the suite verifies the transactional
> validate-then-decrement logic and rejection path. The row-lock guarantee itself is a
> property of MySQL/Postgres and applies when running against the Docker stack.

---

## What I'd do with more time

- **A true concurrency test** spinning up parallel processes against MySQL to assert the
  lock empirically, rather than relying on sequential logic tests.
- **Pagination + server-side filtering** on the product list, with matching cursor-based
  API responses.
- **Auth on write endpoints** (API key or JWT) — currently all endpoints are open, which
  is fine for the exercise scope but not for production.
- **Domain events** (`OrderPlaced`) to decouple side effects like emails or low-stock
  alerts from the order transaction.
- **CI** (lint + test on push) and a production PHP-FPM + nginx image instead of
  `artisan serve`.
- **Optimistic-lock fallback** for high-contention catalogs, chosen per-product.
