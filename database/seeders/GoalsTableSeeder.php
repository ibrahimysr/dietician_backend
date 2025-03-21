<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Goal;
use App\Models\Client;
use App\Models\Dietitian;

class GoalsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = Client::all();
        $dietitians = Dietitian::all();

        if ($clients->isEmpty() || $dietitians->isEmpty()) {
            $this->command->info('Gerekli veriler bulunamadı! ');
            return;
        }

        $goals = [
            [
                'client_id' => $clients->random()->id,
                'dietitian_id' => $dietitians->random()->id,
                'title' => '5 Kilo Vermek',
                'description' => 'Bir ay içinde 5 kilo vermek hedefleniyor.',
                'target_value' => 65.00,
                'current_value' => 70.00,
                'unit' => 'kg',
                'category' => 'weight',
                'start_date' => '2025-03-01',
                'target_date' => '2025-04-01',
                'status' => 'in_progress',
                'priority' => 'high',
                'progress_percentage' => 50.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clients->random()->id,
                'dietitian_id' => $dietitians->random()->id,
                'title' => 'Bel Ölçüsünü Azaltmak',
                'description' => 'Bel ölçüsünü 5 cm azaltmak.',
                'target_value' => 75.00,
                'current_value' => 80.00,
                'unit' => 'cm',
                'category' => 'measurement',
                'start_date' => '2025-03-01',
                'target_date' => '2025-04-01',
                'status' => 'in_progress',
                'priority' => 'medium',
                'progress_percentage' => 40.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clients->random()->id,
                'dietitian_id' => $dietitians->random()->id,
                'title' => 'Günlük Su Tüketimini Artırmak',
                'description' => 'Günde 2 litre su içmek.',
                'target_value' => 2.00,
                'current_value' => 1.50,
                'unit' => 'litre',
                'category' => 'habit',
                'start_date' => '2025-03-01',
                'target_date' => '2025-03-15',
                'status' => 'completed',
                'priority' => 'low',
                'progress_percentage' => 100.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($goals as $goal) {
            $client = Client::find($goal['client_id']);
            if ($client && $client->dietitian_id == $goal['dietitian_id']) {
                Goal::create($goal);
            }
        }

        $this->command->info('Hedefler başarıyla eklendi!');
    }
}