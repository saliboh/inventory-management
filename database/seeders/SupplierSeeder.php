<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Tech Solutions Inc.',
                'email' => 'contact@techsolutions.com',
                'phone' => '(02) 8123-4567',
                'address' => '123 Technology Avenue, Makati City',
                'notes' => 'Primary supplier for electronic devices',
            ],
            [
                'name' => 'Office Essentials Co.',
                'email' => 'sales@officeessentials.com',
                'phone' => '(02) 8234-5678',
                'address' => '456 Business Street, Pasig City',
                'notes' => 'Supplier for office furniture and equipment',
            ],
            [
                'name' => 'Global Imports',
                'email' => 'info@globalimports.com',
                'phone' => '(02) 8345-6789',
                'address' => '789 Trade Road, Quezon City',
                'notes' => 'International supplier for various products',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
