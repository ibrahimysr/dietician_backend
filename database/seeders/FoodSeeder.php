<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Food;
use App\Models\User;

class FoodSeeder extends Seeder
{
       public function run()
    {
        

        $generalFoods = [
            [
                'name' => 'Yulaf Ezmesi',
                'category' => 'Tahıl',
                'serving_size' => 40.00,
                'calories' => 150,
                'protein' => 5.00,
                'fat' => 2.50,
                'carbs' => 27.00,
                'fiber' => 4.00,
                'sugar' => 1.00,
                'is_custom' => false,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Elma',
                'category' => 'Meyve',
                'serving_size' => 182.00,
                'calories' => 95,
                'protein' => 0.50,
                'fat' => 0.30,
                'carbs' => 25.10,
                'fiber' => 4.40,
                'sugar' => 18.90,
                'is_custom' => false,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tavuk Göğsü',
                'category' => 'Et',
                'serving_size' => 100.00,
                'calories' => 165,
                'protein' => 31.00,
                'fat' => 3.60,
                'carbs' => 0.00,
                'fiber' => 0.00,
                'sugar' => 0.00,
                'is_custom' => false,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Badem',
                'category' => 'Kuruyemiş',
                'serving_size' => 28.00,
                'calories' => 160,
                'protein' => 6.00,
                'fat' => 14.00,
                'carbs' => 6.00,
                'fiber' => 3.50,
                'sugar' => 1.00,
                'is_custom' => false,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Süt (Tam Yağlı)',
                'category' => 'Süt Ürünleri',
                'serving_size' => 240.00,
                'calories' => 150,
                'protein' => 8.00,
                'fat' => 8.00,
                'carbs' => 12.00,
                'fiber' => 0.00,
                'sugar' => 12.00,
                'is_custom' => false,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $customFoods = [
            [
                'name' => 'Özel Protein Shake',
                'category' => 'İçecek',
                'serving_size' => 300.00,
                'calories' => 200,
                'protein' => 30.00,
                'fat' => 5.00,
                'carbs' => 10.00,
                'fiber' => 2.00,
                'sugar' => 5.00,
                'is_custom' => true,
                'created_by' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ev Yapımı Granola',
                'category' => 'Atıştırmalık',
                'serving_size' => 50.00,
                'calories' => 220,
                'protein' => 6.00,
                'fat' => 10.00,
                'carbs' => 28.00,
                'fiber' => 4.00,
                'sugar' => 8.00,
                'is_custom' => true,
                'created_by' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Diyetisyen Salatası',
                'category' => 'Salata',
                'serving_size' => 200.00,
                'calories' => 120,
                'protein' => 5.00,
                'fat' => 8.00,
                'carbs' => 10.00,
                'fiber' => 3.00,
                'sugar' => 2.00,
                'is_custom' => true,
                'created_by' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($generalFoods as $food) {
            Food::firstOrCreate(
                ['name' => $food['name'], 'is_custom' => false],
                $food
            );
        }

        foreach ($customFoods as $food) {
            Food::firstOrCreate(
                ['name' => $food['name'], 'created_by' => $food['created_by']],
                $food
            );
        }
    }
}