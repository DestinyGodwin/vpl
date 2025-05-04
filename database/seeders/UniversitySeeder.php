<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        {
            $unis = [
                ['name'=>'University of Lagos','address'=>'Akoka, Yaba','state'=>'Lagos','country'=>'Nigeria'],
                ['name'=>'Obafemi Awolowo University','address'=>'Ile‐Ife','state'=>'Osun','country'=>'Nigeria'],
                ['name'=>'University of Ibadan','address'=>'Ibadan','state'=>'Oyo','country'=>'Nigeria'],
            ];
            foreach($unis as $u) University::create($u);
    }
}
