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
        $categories =  ['name' => 'Phones', 'store_type' => 'regular'],
        ['name' => 'Laptops', 'store_type' => 'regular'],
        ['name' => 'Pizza', 'store_type' => 'food'],
        ['name' => 'Burgers', 'store_type' => 'food'],

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['name' => $name]
                [ 'store_type' => $data['store_type']]
            );
        }
    }
}
