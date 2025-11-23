<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Tablets', 'description' => 'Solid dosage forms containing medicinal substances.'],
            ['name' => 'Syrups', 'description' => 'Liquid preparations.'],
            ['name' => 'Injections', 'description' => 'Sterile solutions.'],
            ['name' => 'Ointments', 'description' => 'Semisolid preparations.'],
            ['name' => 'Equipment', 'description' => 'Medical devices.'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
