<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop',
                'description' => 'High-performance laptop for professional use',
                'sku' => 'LAP-001',
                'price' => 45000.00,
            ],
            [
                'name' => 'Smartphone',
                'description' => 'Latest model smartphone with advanced features',
                'sku' => 'SPH-001',
                'price' => 25000.00,
            ],
            [
                'name' => 'Office Chair',
                'description' => 'Ergonomic office chair for comfortable work experience',
                'sku' => 'FRN-001',
                'price' => 8500.00,
            ],
            [
                'name' => 'Printer',
                'description' => 'High-speed color laser printer',
                'sku' => 'PRT-001',
                'price' => 15000.00,
            ],
            [
                'name' => 'USB Drive',
                'description' => '32GB USB 3.0 flash drive',
                'sku' => 'USB-001',
                'price' => 800.00,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
