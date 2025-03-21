<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Progress;
use App\Models\Client;

class ProgressTableSeeder extends Seeder
{
    
    public function run()
    {
        $clients = Client::all();

        if ($clients->isEmpty()) {
            $this->command->info('Gerekli veriler bulunamadı!');
            return;
        }

        $progressRecords = [
            [
                'client_id' => $clients->random()->id,
                'date' => '2025-03-01',
                'weight' => 70.50,
                'waist' => 80.00,
                'arm' => 30.00,
                'chest' => 95.00,
                'hip' => 90.00,
                'body_fat_percentage' => 20.50,
                'notes' => 'İlk ölçüm, diyete yeni başlandı.',
                'photo_url' => 'https://example.com/photos/progress-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clients->random()->id,
                'date' => '2025-03-15',
                'weight' => 69.00,
                'waist' => 78.50,
                'arm' => 29.50,
                'chest' => 94.00,
                'hip' => 89.00,
                'body_fat_percentage' => 19.80,
                'notes' => 'İki hafta sonra iyi bir ilerleme kaydedildi.',
                'photo_url' => 'https://example.com/photos/progress-2.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => $clients->random()->id,
                'date' => '2025-03-30',
                'weight' => 68.00,
                'waist' => 77.00,
                'arm' => 29.00,
                'chest' => 93.00,
                'hip' => 88.00,
                'body_fat_percentage' => 19.00,
                'notes' => 'Bir ay sonunda hedefe yaklaşıldı.',
                'photo_url' => 'https://example.com/photos/progress-3.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($progressRecords as $progress) {
            Progress::create($progress);
        }

        $this->command->info('İlerleme kayıtları başarıyla eklendi!');
    }
}