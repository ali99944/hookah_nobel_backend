<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'الشيش والأرجيلة',
                'slug' => 'hookahs',
                'description' => 'تشكيلة متنوعة من الشيش التقليدية والحديثة بتصاميم فاخرة وجودة عالية لتجربة تدخين استثنائية',
                'is_active' => true,
            ],
            [
                'name' => 'رؤوس الشيشة',
                'slug' => 'bowls',
                'description' => 'رؤوس شيشة متعددة من السيراميك والطين الحراري بأشكال وأحجام مختلفة لتوزيع حرارة مثالي',
                'is_active' => true,
            ],
            [
                'name' => 'خراطيم وأنابيب',
                'slug' => 'hoses',
                'description' => 'خراطيم شيشة عالية الجودة قابلة للغسل بتصاميم عصرية وألوان متنوعة لراحة وسلاسة في الاستخدام',
                'is_active' => true,
            ],
            [
                'name' => 'الفحم',
                'slug' => 'charcoal',
                'description' => 'فحم طبيعي من جوز الهند وفحم سريع الاشتعال خالي من الروائح الكريهة لجلسة نظيفة وطويلة',
                'is_active' => true,
            ],
            [
                'name' => 'المعسل والنكهات',
                'slug' => 'flavors',
                'description' => 'مجموعة واسعة من نكهات المعسل الفاخرة بجودة عالية وطعم غني من أشهر العلامات التجارية العالمية',
                'is_active' => true,
            ],
            [
                'name' => 'الإكسسوارات',
                'slug' => 'accessories',
                'description' => 'جميع إكسسوارات الشيشة من ملاقط وأطباق فحم ومبسم وحوامل وقطع غيار لتجربة مثالية ومريحة',
                'is_active' => true,
            ],
            [
                'name' => 'أجهزة التسخين',
                'slug' => 'heat-management',
                'description' => 'أجهزة إدارة الحرارة الحديثة لتوزيع متساوي للحرارة والحفاظ على نكهة المعسل لأطول فترة ممكنة',
                'is_active' => true,
            ],
            [
                'name' => 'القواعد والزجاج',
                'slug' => 'bases',
                'description' => 'قواعد شيشة زجاجية فاخرة بتصاميم فنية راقية ومقاومة للكسر لإضفاء لمسة جمالية على جلستك',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
