<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recipe;
use App\Models\Dietitian;

class RecipesTableSeeder extends Seeder
{
   
    public function run()
    {
        $dietitians = Dietitian::all();

        if ($dietitians->isEmpty()) {
            $this->command->info('Gerekli veriler bulunamadı!');
            return;
        }

        $recipes = [
            [
                'dietitian_id' => $dietitians->random()->id,
                'title' => 'Avokado Salatası',
                'description' => 'Hafif ve sağlıklı bir avokado salatası tarifi.',
                'ingredients' => [
                    '1 adet avokado',
                    '1 adet domates',
                    '1/2 adet kırmızı soğan',
                    '1 yemek kaşığı zeytinyağı',
                    '1/2 limon suyu',
                    'Tuz ve karabiber'
                ],
                'instructions' => "1. Avokadoyu ikiye bölün, çekirdeğini çıkarın ve küp küp doğrayın.\n2. Domatesi ve kırmızı soğanı küçük küçük doğrayın.\n3. Tüm malzemeleri bir kasede karıştırın.\n4. Üzerine zeytinyağı, limon suyu, tuz ve karabiber ekleyip servis yapın.",
                'prep_time' => 10,
                'cook_time' => 0,
                'servings' => 2,
                'calories' => 200,
                'protein' => 2.50,
                'fat' => 15.00,
                'carbs' => 10.00,
                'tags' => 'vegan,gluten-free,healthy',
                'photo_url' => 'https://example.com.jpg',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dietitian_id' => $dietitians->random()->id,
                'title' => 'Tavuklu Quinoa Bowl',
                'description' => 'Protein dolu bir öğün için tavuklu quinoa bowl.',
                'ingredients' => [
                    '100 gr tavuk göğsü',
                    '1/2 su bardağı quinoa',
                    '1 adet avokado',
                    '1 su bardağı ıspanak',
                    '1 yemek kaşığı zeytinyağı',
                    'Tuz ve baharatlar'
                ],
                'instructions' => "1. Quinoa’yı paket talimatlarına göre pişirin.\n2. Tavuk göğsünü ızgarada pişirin ve dilimleyin.\n3. Ispanakları yıkayın ve bir kaseye alın.\n4. Üzerine quinoa, tavuk ve avokado dilimlerini ekleyin.\n5. Zeytinyağı, tuz ve baharatlarla tatlandırıp servis yapın.",
                'prep_time' => 15,
                'cook_time' => 20,
                'servings' => 1,
                'calories' => 450,
                'protein' => 30.00,
                'fat' => 20.00,
                'carbs' => 35.00,
                'tags' => 'high-protein,gluten-free',
                'photo_url' => 'https://example.com.jpg',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dietitian_id' => $dietitians->random()->id,
                'title' => 'Meyveli Smoothie',
                'description' => 'Sabahları enerji veren bir meyveli smoothie tarifi.',
                'ingredients' => [
                    '1 adet muz',
                    '1/2 su bardağı çilek',
                    '1/2 su bardağı yulaf sütü',
                    '1 yemek kaşığı chia tohumu',
                    '1 tatlı kaşığı bal'
                ],
                'instructions' => "1. Tüm malzemeleri blender’a koyun.\n2. Pürüzsüz bir kıvam alana kadar blend edin.\n3. Bardakta servis yapın.",
                'prep_time' => 5,
                'cook_time' => 0,
                'servings' => 1,
                'calories' => 180,
                'protein' => 3.00,
                'fat' => 5.00,
                'carbs' => 30.00,
                'tags' => 'vegan,quick,breakfast',
                'photo_url' => 'https://example.com.jpg',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($recipes as $recipe) {
            Recipe::create($recipe);
        }

        $this->command->info('Tarifler başarıyla eklendi!');
    }
}