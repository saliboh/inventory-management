<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'full_name' => 'John Doe',
                'contact_number' => '09123456789',
                'position' => 'Warehouse Manager',
                'notes' => 'Main warehouse supervisor',
            ],
            [
                'full_name' => 'Jane Smith',
                'contact_number' => '09234567890',
                'position' => 'Inventory Specialist',
                'notes' => 'Handles inventory audits',
            ],
            [
                'full_name' => 'Mike Johnson',
                'contact_number' => '09345678901',
                'position' => 'Logistics Coordinator',
                'notes' => 'Manages product shipments',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
