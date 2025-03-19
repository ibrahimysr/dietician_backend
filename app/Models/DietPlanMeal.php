<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DietPlanMeal extends Model
{
    use SoftDeletes;

    protected $table = 'diet_plan_meals'; 

    protected $fillable = [
        'diet_plan_id',
        'day_number',
        'meal_type',
        'description',
        'calories',
        'protein',
        'fat',
        'carbs',
        'photo_url',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'calories' => 'integer',
        'protein' => 'decimal:2',
        'fat' => 'decimal:2',
        'carbs' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function dietPlan()
    {
        return $this->belongsTo(DietPlan::class, 'diet_plan_id');
    }
}