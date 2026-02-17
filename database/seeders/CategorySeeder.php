<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Hookahs
            [
                'name' => 'الشيش',
                'slug' => 'hookahs',
            ],

            // Tobacco / Flavors
            [
                'name' => 'المعسل والنكهات',
                'slug' => 'flavors',
            ],

            // Charcoal
            [
                'name' => 'الفحم',
                'slug' => 'charcoal',
            ],

            // Accessories
            [
                'name' => 'الإكسسوارات',
                'slug' => 'accessories',
            ],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
