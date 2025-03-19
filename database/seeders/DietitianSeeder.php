<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DietitianSeeder extends Seeder
{
    public function run()
    {
        DB::table('dietitians')->insert([
            [
                'user_id' => 4,
                'specialty' => 'Sporcu Beslenmesi',
                'bio' => 'Profesyonel sporcular için beslenme programları hazırlıyorum.',
                'hourly_rate' => 200.00,
                'experience_years' => 8,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
           
           
        ]);
    }
}
