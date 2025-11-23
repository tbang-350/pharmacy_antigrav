<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'PharmaDistro Ltd', 'contact_info' => '123-456-7890', 'address' => '123 Main St'],
            ['name' => 'MediSupply Co', 'contact_info' => '987-654-3210', 'address' => '456 Oak Ave'],
            ['name' => 'Global Health Inc', 'contact_info' => '555-123-4567', 'address' => '789 Pine Rd'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
