<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'location' => 'Manila',
                'description' => 'Primary storage facility for all products',
            ],
            [
                'name' => 'South Branch',
                'location' => 'Cebu',
                'description' => 'Regional warehouse for southern distribution',
            ],
            [
                'name' => 'North Branch',
                'location' => 'Baguio',
                'description' => 'Regional warehouse for northern distribution',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}
