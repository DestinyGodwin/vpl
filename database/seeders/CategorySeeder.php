<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Electronics',
            'Groceries',
            'Clothing',
            'Restaurants',
            'Bakery',
            'Fast Food',
            'Home Appliances',
            'Beauty & Health',
            'Books',
            'Toys',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['name' => $name]
          
            );
        }
    }
}
