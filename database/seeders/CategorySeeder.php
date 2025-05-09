<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories =  [
            ['name' => 'Phones', 'store_type' => 'regular'],
        ['name' => 'Laptops', 'store_type' => 'regular'],
        ['name' => 'Pizza', 'store_type' => 'food'],
        ['name' => 'Burgers', 'store_type' => 'food'],];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['name' => $data['name']],
                [ 'store_type' => $data['store_type']]
            );
        }
    }
}
