<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Client;

class PaymentsTableSeeder extends Seeder
{
    
    public function run()
    {
        $subscriptions = Subscription::all();
        $clients = Client::all();

        if ($subscriptions->isEmpty() || $clients->isEmpty()) {
            $this->command->info('Gerekli veriler bulunamadı!');
            return;
        }

        $payments = [
            [
                'subscription_id' => $subscriptions->random()->id,
                'client_id' => $subscriptions->random()->client_id,
                'amount' => 99.99,
                'currency' => 'TRY',
                'payment_date' => '2025-03-20 10:00:00',
                'payment_method' => 'credit_card',
                'transaction_id' => 'TXN-001',
                'status' => 'completed',
                'refund_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subscription_id' => $subscriptions->random()->id,
                'client_id' => $subscriptions->random()->client_id,
                'amount' => 249.99,
                'currency' => 'TRY',
                'payment_date' => '2025-03-21 12:00:00',
                'payment_method' => 'bank_transfer',
                'transaction_id' => 'TXN-002',
                'status' => 'pending',
                'refund_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'subscription_id' => $subscriptions->random()->id,
                'client_id' => $subscriptions->random()->client_id,
                'amount' => 29.99,
                'currency' => 'TRY',
                'payment_date' => '2025-03-22 15:00:00',
                'payment_method' => 'credit_card',
                'transaction_id' => 'TXN-003',
                'status' => 'refunded',
                'refund_date' => '2025-03-23 09:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($payments as $payment) {
            $subscription = Subscription::find($payment['subscription_id']);
            if ($subscription && $subscription->client_id == $payment['client_id']) {
                Payment::create($payment);
            }
        }

        $this->command->info('Ödemeler başarıyla eklendi!');
    }
}