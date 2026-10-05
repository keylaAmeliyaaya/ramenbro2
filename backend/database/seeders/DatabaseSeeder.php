<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Signature Tonkotsu Ramen', 'price' => 85000, 'category' => 'ramen'],
            ['name' => 'Shoyu Ramen', 'price' => 78000, 'category' => 'ramen'],
            ['name' => 'Miso Ramen', 'price' => 82000, 'category' => 'ramen'],
            ['name' => 'Original Mazemen', 'price' => 75000, 'category' => 'dry-ramen'],
            ['name' => 'Karaage Mazesoba', 'price' => 85000, 'category' => 'dry-ramen'],
            ['name' => 'Spicy Garlic Mazesoba', 'price' => 80000, 'category' => 'dry-ramen'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => Str::slug($product['name'])],
                [
                    ...$product,
                    'slug' => Str::slug($product['name']),
                    'description' => null,
                    'stock' => 100,
                    'is_active' => true,
                ]
            );
        }

        $materials = [
            ['name' => 'Mie Ramen', 'unit' => 'pcs', 'stock' => 100, 'minimum_stock' => 20, 'supplier' => 'Supplier Mie'],
            ['name' => 'Kaldu Tonkotsu', 'unit' => 'liter', 'stock' => 50, 'minimum_stock' => 10, 'supplier' => 'Supplier Kaldu'],
            ['name' => 'Telur', 'unit' => 'pcs', 'stock' => 100, 'minimum_stock' => 20, 'supplier' => 'Supplier Telur'],
        ];

        foreach ($materials as $material) {
            Material::updateOrCreate(
                ['name' => $material['name']],
                [...$material, 'price' => 0, 'is_active' => true]
            );
        }
    }
}
