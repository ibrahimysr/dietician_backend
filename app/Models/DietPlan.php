<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DietPlan extends Model
{
    use SoftDeletes;

    protected $table = 'diet_plans';

    protected $fillable = [
        'client_id',
        'dietitian_id',
        'title',
        'start_date',
        'end_date',
        'daily_calories',
        'notes',
        'status',
        'is_ongoing',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'daily_calories' => 'integer',
        'is_ongoing' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function dietitian()
    {
        return $this->belongsTo(Dietitian::class, 'dietitian_id');
    }
    public function meals()
{
    return $this->hasMany(DietPlanMeal::class, 'diet_plan_id');
}
}