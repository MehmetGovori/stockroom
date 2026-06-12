<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_it_filters_products_by_search(): void
    {
        Product::factory()->create(['name' => 'Copper Pan', 'sku' => 'PAN-0001']);
        Product::factory()->create(['name' => 'Linen Apron', 'sku' => 'APR-0002']);

        $response = $this->getJson('/api/products?search=copper');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'PAN-0001');
    }

    public function test_it_creates_a_product(): void
    {
        $payload = [
            'name' => 'Stoneware Bowl',
            'sku' => 'BWL-0500',
            'price' => 24.5,
            'stock_quantity' => 15,
            'category' => 'Ceramics',
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.sku', 'BWL-0500')
            ->assertJsonPath('data.price', fn ($price) => (float) $price === 24.5);

        $this->assertDatabaseHas('products', ['sku' => 'BWL-0500', 'stock_quantity' => 15]);
    }

    public function test_it_validates_product_creation(): void
    {
        $response = $this->postJson('/api/products', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'sku', 'price', 'stock_quantity', 'category']);
    }

    public function test_it_rejects_a_duplicate_sku(): void
    {
        Product::factory()->create(['sku' => 'DUP-0001']);

        $response = $this->postJson('/api/products', [
            'name' => 'Another',
            'sku' => 'DUP-0001',
            'price' => 10,
            'stock_quantity' => 5,
            'category' => 'Home',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['sku']);
    }

    public function test_it_updates_a_product(): void
    {
        $product = Product::factory()->create(['price' => 10]);

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => 99.99,
            'stock_quantity' => 7,
            'category' => $product->category,
        ]);

        $response->assertOk()->assertJsonPath('data.price', fn ($price) => (float) $price === 99.99);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 7]);
    }

    public function test_it_deletes_a_product(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson("/api/products/{$product->id}")->assertNoContent();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
