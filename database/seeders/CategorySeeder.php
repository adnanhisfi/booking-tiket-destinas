<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Pantai', 'description' => 'Destinasi wisata bahari dan pantai'],
            ['name' => 'Gunung', 'description' => 'Pendakian dan wisata pegunungan'],
            ['name' => 'Taman Hiburan', 'description' => 'Theme park dan wahana keluarga'],
            ['name' => 'Budaya & Sejarah', 'description' => 'Candi, museum, dan situs budaya'],
            ['name' => 'Air Terjun', 'description' => 'Wisata air terjun dan sungai'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category + ['slug' => Str::slug($category['name'])]
            );
        }
    }
}
