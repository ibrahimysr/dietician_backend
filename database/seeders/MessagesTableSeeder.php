<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\User;

class MessagesTableSeeder extends Seeder
{
   
    public function run()
    {
        $users = User::all();

        if ($users->count() < 2) {
            $this->command->info('Gerekli veriler bulunamadı!');
            return;
        }

        $messages = [
            [
                'sender_id' => $users[0]->id,
                'receiver_id' => $users[1]->id,
                'message' => 'Merhaba, nasılsın?',
                'attachment_url' => null,
                'sent_at' => now()->subMinutes(10),
                'read_at' => now()->subMinutes(5),
                'is_delivered' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sender_id' => $users[1]->id,
                'receiver_id' => $users[0]->id,
                'message' => 'İyiyim, teşekkür ederim! Sen nasılsın?',
                'attachment_url' => null,
                'sent_at' => now()->subMinutes(8),
                'read_at' => now()->subMinutes(3),
                'is_delivered' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sender_id' => $users[0]->id,
                'receiver_id' => $users[1]->id,
                'message' => 'Bu diyeti nasıl buldun? İşte bir örnek.',
                'attachment_url' => 'https://example.pdf',
                'sent_at' => now()->subMinutes(2),
                'read_at' => null,
                'is_delivered' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($messages as $message) {
            Message::create($message);
        }

        $this->command->info('Mesajlar başarıyla eklendi!');
    }
}