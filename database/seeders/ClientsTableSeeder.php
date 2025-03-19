<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ClientsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('clients')->insert([
            [
                'user_id' => 1, 
                'dietitian_id' => 1, 
                'birth_date' => '1990-05-15',
                'gender' => 'male',
                'height' => 175.50,
                'weight' => 70.25,
                'activity_level' => 'moderate',
                'goal' => 'Muscle gain',
                'allergies' => 'Peanuts, Dairy',
                'preferences' => 'Vegetarian',
                'medical_conditions' => 'None',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 2, 
                'dietitian_id' => null, 
                'birth_date' => '1985-09-20',
                'gender' => 'male',
                'height' => 165.30,
                'weight' => 58.70,
                'activity_level' => 'active',
                'goal' => 'Weight loss',
                'allergies' => 'None',
                'preferences' => 'Keto diet',
                'medical_conditions' => 'Diabetes',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
        ]);
    }
}
