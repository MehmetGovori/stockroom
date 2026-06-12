<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function place(array $items): Order
    {
        $quantities = $this->mergeQuantities($items);

        return DB::transaction(function () use ($quantities) {
            $products = Product::query()
                ->whereIn('id', array_keys($quantities))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $this->guardAgainstShortfalls($products, $quantities);

            $total = '0.00';
            $lines = [];

            foreach ($quantities as $productId => $quantity) {
                $product = $products[$productId];
                $lineTotal = bcmul((string) $product->price, (string) $quantity, 2);
                $total = bcadd($total, $lineTotal, 2);

                $product->decrement('stock_quantity', $quantity);

                $lines[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                ];
            }

            $order = Order::create([
                'status' => 'confirmed',
                'total' => $total,
            ]);

            $order->items()->createMany($lines);

            return $order->load('items');
        });
    }

    private function mergeQuantities(array $items): array
    {
        $merged = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $merged[$productId] = ($merged[$productId] ?? 0) + (int) $item['quantity'];
        }

        return $merged;
    }

    private function guardAgainstShortfalls($products, array $quantities): void
    {
        $shortfalls = [];

        foreach ($quantities as $productId => $quantity) {
            $product = $products->get($productId);
            $available = $product?->stock_quantity ?? 0;

            if ($available < $quantity) {
                $shortfalls[] = [
                    'product_id' => $productId,
                    'requested' => $quantity,
                    'available' => $available,
                ];
            }
        }

        if ($shortfalls !== []) {
            throw new InsufficientStockException($shortfalls);
        }
    }
}
