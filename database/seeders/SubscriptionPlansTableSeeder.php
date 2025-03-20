<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use App\Models\Dietitian;

class SubscriptionPlansTableSeeder extends Seeder
{
    
    public function run()
    {
        $dietitians = Dietitian::all();

        if ($dietitians->isEmpty()) {
            $this->command->info('Diyetisyen bulunamadı! Lütfen önce DietitiansTableSeeder\'ı çalıştırın.');
            return;
        }

        $subscriptionPlans = [
            [
                'dietitian_id' => $dietitians->random()->id,
                'name' => 'Temel Plan',
                'description' => 'Haftalık takip ve temel diyet planı içerir.',
                'duration' => 30,
                'price' => 99.99,
                'features' => json_encode(['Haftalık takip', 'Temel diyet planı']),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dietitian_id' => $dietitians->random()->id,
                'name' => 'Premium Plan',
                'description' => 'Haftalık takip, kişiselleştirilmiş diyet planı ve birebir görüşme içerir.',
                'duration' => 90,
                'price' => 249.99,
                'features' => json_encode(['Haftalık takip', 'Kişiselleştirilmiş diyet planı', 'Birebir görüşme']),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dietitian_id' => $dietitians->random()->id,
                'name' => 'Deneme Planı',
                'description' => 'Kısa süreli deneme planı, sınırlı özellikler içerir.',
                'duration' => 7,
                'price' => 29.99,
                'features' => json_encode(['Haftalık takip']),
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($subscriptionPlans as $plan) {
            SubscriptionPlan::create($plan);
        }

        $this->command->info('Abonelik planları başarıyla eklendi!');
    }
}