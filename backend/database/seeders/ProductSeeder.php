<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Brushed Steel Kettle', 'sku' => 'KTL-0042', 'price' => 79.00, 'stock_quantity' => 24, 'category' => 'Kitchen'],
            ['name' => 'Cast Iron Skillet 12"', 'sku' => 'SKL-0112', 'price' => 45.50, 'stock_quantity' => 12, 'category' => 'Kitchen'],
            ['name' => 'Walnut Cutting Board', 'sku' => 'BRD-0301', 'price' => 38.00, 'stock_quantity' => 3, 'category' => 'Kitchen'],
            ['name' => 'Linen Apron', 'sku' => 'APR-0210', 'price' => 29.99, 'stock_quantity' => 40, 'category' => 'Textiles'],
            ['name' => 'Waffle Hand Towel Set', 'sku' => 'TWL-0188', 'price' => 22.00, 'stock_quantity' => 0, 'category' => 'Textiles'],
            ['name' => 'Stoneware Mug', 'sku' => 'MUG-0077', 'price' => 16.00, 'stock_quantity' => 60, 'category' => 'Ceramics'],
            ['name' => 'Speckled Dinner Plate', 'sku' => 'PLT-0091', 'price' => 19.50, 'stock_quantity' => 2, 'category' => 'Ceramics'],
            ['name' => 'Beeswax Pillar Candle', 'sku' => 'CDL-0455', 'price' => 14.00, 'stock_quantity' => 85, 'category' => 'Home'],
            ['name' => 'Rattan Storage Basket', 'sku' => 'BSK-0623', 'price' => 52.00, 'stock_quantity' => 5, 'category' => 'Home'],
            ['name' => 'Brass Watering Can', 'sku' => 'WTR-0509', 'price' => 64.00, 'stock_quantity' => 9, 'category' => 'Garden'],
            ['name' => 'Terracotta Plant Pot', 'sku' => 'POT-0144', 'price' => 12.50, 'stock_quantity' => 30, 'category' => 'Garden'],
            ['name' => 'Leather Care Kit', 'sku' => 'LTH-0388', 'price' => 34.00, 'stock_quantity' => 1, 'category' => 'Care'],
            ['name' => 'Enamel Pour-Over Pot', 'sku' => 'KTL-0067', 'price' => 58.00, 'stock_quantity' => 14, 'category' => 'Kitchen'],
            ['name' => 'Olive Wood Spoon Set', 'sku' => 'UTN-0231', 'price' => 26.00, 'stock_quantity' => 22, 'category' => 'Kitchen'],
            ['name' => 'Striped Tea Towel', 'sku' => 'TWL-0204', 'price' => 13.50, 'stock_quantity' => 4, 'category' => 'Textiles'],
            ['name' => 'Glazed Serving Bowl', 'sku' => 'BWL-0118', 'price' => 41.00, 'stock_quantity' => 7, 'category' => 'Ceramics'],
            ['name' => 'Soy Wax Tin Candle', 'sku' => 'CDL-0461', 'price' => 18.00, 'stock_quantity' => 0, 'category' => 'Home'],
            ['name' => 'Hand Trowel', 'sku' => 'GRD-0742', 'price' => 21.00, 'stock_quantity' => 16, 'category' => 'Garden'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['sku' => $product['sku']], $product);
        }
    }
}
