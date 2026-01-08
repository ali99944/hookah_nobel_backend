<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $hookahCat = Category::where('slug', 'hookahs')->first();

        if ($hookahCat) {
            $product = Product::create([
                'category_id' => $hookahCat->id,
                'name' => 'شيشة خليل مأمون كلاسيك',
                'slug' => 'khalil-mamoon-classic',
                'description' => 'شيشة مصرية أصلية مصنوعة يدوياً من النحاس.',
                'price' => 1200.00,
                'stock' => 10,
                'status' => 'active',
            ]);

            $product->attributes()->createMany([
                ['key' => 'الارتفاع', 'value' => '75 سم'],
                ['key' => 'المادة', 'value' => 'نحاس أصفر'],
                ['key' => 'بلد المنشأ', 'value' => 'مصر'],
            ]);

            $product->features()->createMany([
                ['key' => 'تصميم يدوي', 'value' => 'نقوش يدوية مميزة تجعل كل قطعة فريدة.'],
                ['key' => 'سحب ممتاز', 'value' => 'غرفة هواء واسعة لسحب دخان كثيف وسلس.'],
            ]);
        }
    }
}
