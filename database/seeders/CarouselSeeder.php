<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carousel;
use App\Models\Product;

class CarouselSeeder extends Seeder
{
    public function run(): void
    {

        $products = Product::inRandomOrder()->take(3)->get();

        $carouselData = [
            [
                'title' => [
                    'en' => 'Summer Collection',
                    'ar' => 'مجموعة الصيف',
                ],
                'subtitle' => [
                    'en' => 'Fresh styles for hot days',
                    'ar' => 'أنماط جديدة لأيام الصيف الحارة',
                ],
                'image' => public_path('images/carousel1.jpg'),
                'mobile_image' => public_path('images/carousel1.jpg'),
            ],
            [
                'title' => [
                    'en' => 'Winter Essentials',
                    'ar' => 'أساسيات الشتاء',
                ],
                'subtitle' => [
                    'en' => 'Warm up your wardrobe',
                    'ar' => 'أدفئ خزانة ملابسك',
                ],
                'image' => public_path('images/carousel2.jpg'),
                'mobile_image' => public_path('images/carousel2.jpg'),
            ],
            [
                'title' => [
                    'en' => 'Ramadan Specials',
                    'ar' => 'عروض رمضان',
                ],
                'subtitle' => [
                    'en' => 'Celebrate with savings',
                    'ar' => 'احتفل مع التخفيضات',
                ],
                'image' => public_path('images/carousel3.jpg'),
                'mobile_image' => public_path('images/carousel3.jpg'),
            ],
        ];

        foreach ($products as $i => $product) {
            $carousel = Carousel::create([
                'title' => $carouselData[$i]['title'],
                'subtitle' => $carouselData[$i]['subtitle'],
                'product_id' => $product->id,
            ]);

// Attach image (if exists)
                $carousel->addMedia($carouselData[$i]['image'])->toMediaCollection('carousels');
                $carousel->addMedia($carouselData[$i]['mobile_image'])->toMediaCollection('mobile_carousels');
        }
    }
}
