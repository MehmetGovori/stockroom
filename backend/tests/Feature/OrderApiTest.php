<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_places_an_order_decrements_stock_and_computes_total(): void
    {
        $kettle = Product::factory()->price(79.00)->stock(10)->create();
        $mug = Product::factory()->price(16.00)->stock(60)->create();

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $kettle->id, 'quantity' => 2],
                ['product_id' => $mug->id, 'quantity' => 3],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.total', 206)
            ->assertJsonCount(2, 'data.items');

        $this->assertDatabaseHas('products', ['id' => $kettle->id, 'stock_quantity' => 8]);
        $this->assertDatabaseHas('products', ['id' => $mug->id, 'stock_quantity' => 57]);
    }

    public function test_it_rejects_an_order_that_exceeds_stock_and_leaves_stock_untouched(): void
    {
        $product = Product::factory()->stock(3)->create();

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('errors.items.0.product_id', $product->id)
            ->assertJsonPath('errors.items.0.requested', 5)
            ->assertJsonPath('errors.items.0.available', 3);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 3]);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_it_validates_an_empty_order(): void
    {
        $this->postJson('/api/orders', ['items' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_it_merges_duplicate_lines_for_the_same_product(): void
    {
        $product = Product::factory()->price(10.00)->stock(10)->create();

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('data.total', 50);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 5]);
    }

    public function test_two_orders_for_the_last_unit_cannot_both_succeed(): void
    {
        $product = Product::factory()->stock(1)->create();

        $first = $this->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);
        $second = $this->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $first->assertCreated();
        $second->assertStatus(409);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 0]);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_it_lists_orders_with_items_and_totals(): void
    {
        $product = Product::factory()->price(20.00)->stock(10)->create();

        $this->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertCreated();

        $response = $this->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.total', 40)
            ->assertJsonCount(1, 'data.0.items');

        $this->assertSame(1, Order::query()->count());
    }
}
