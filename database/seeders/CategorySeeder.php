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

            // Bowls
            [
                'name' => 'رؤوس الشيشة',
                'slug' => 'bowls',
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

            // Heating Tools
            [
                'name' => 'التسخين',
                'slug' => 'heating',
            ],

            // Hoses
            [
                'name' => 'الخراطيم',
                'slug' => 'hoses',
            ],

            // Mouthpieces
            [
                'name' => 'المباسم',
                'slug' => 'mouthpieces',
            ],

            // Spare Parts
            [
                'name' => 'قطع الغيار',
                'slug' => 'spare-parts',
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
