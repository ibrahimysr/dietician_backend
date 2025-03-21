<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use App\Models\Client;
use App\Models\Dietitian;
use App\Models\SubscriptionPlan;

class SubscriptionsTableSeeder extends Seeder
{
    public function run()
    {
        $clients = Client::all();
        $dietitians = Dietitian::all();
        $subscriptionPlans = SubscriptionPlan::all();

        if ($clients->isEmpty() || $dietitians->isEmpty() || $subscriptionPlans->isEmpty()) {
            $this->command->info('Gerekli veriler bulunamadı! Lütfen önce ClientsTableSeeder, DietitiansTableSeeder ve SubscriptionPlansTableSeeder\'ı çalıştırın.');
            return;
        }

        $subscriptions = [
            [
                'client_id' => $clients->random()->id,
                'dietitian_id' => $dietitians->random()->id,
                'subscription_plan_id' => $subscriptionPlans->where('dietitian_id', $dietitians->random()->id)->first()->id,
                'start_date' => '2025-03-20',
                'end_date' => '2025-04-19',
                'status' => 'active',
                'auto_renew' => false,
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clients->random()->id,
                'dietitian_id' => $dietitians->random()->id,
                'subscription_plan_id' => $subscriptionPlans->where('dietitian_id', $dietitians->random()->id)->first()->id,
                'start_date' => '2025-03-15',
                'end_date' => '2025-06-13', 
                'status' => 'active',
                'auto_renew' => true,
                'payment_status' => 'unpaid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clients->random()->id,
                'dietitian_id' => $dietitians->random()->id,
                'subscription_plan_id' => $subscriptionPlans->where('dietitian_id', $dietitians->random()->id)->first()->id,
                'start_date' => '2025-03-10',
                'end_date' => '2025-03-17', 
                'status' => 'expired',
                'auto_renew' => false,
                'payment_status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($subscriptions as $subscription) {
            $subscriptionPlan = SubscriptionPlan::find($subscription['subscription_plan_id']);
            if ($subscriptionPlan && $subscriptionPlan->dietitian_id == $subscription['dietitian_id']) {
                $client = Client::find($subscription['client_id']);
                if ($client && $client->dietitian_id == $subscription['dietitian_id']) {
                    Subscription::create($subscription);
                }
            }
        }

        $this->command->info('Abonelikler başarıyla eklendi!');
    }
}