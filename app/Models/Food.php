<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Food extends Model
{
    use SoftDeletes;

    protected $table = 'foods'; 

    protected $fillable = [
        'name',
        'category',
        'serving_size',
        'calories',
        'protein',
        'fat',
        'carbs',
        'fiber',
        'sugar',
        'is_custom',
        'created_by',
    ];

    protected $casts = [
        'serving_size' => 'decimal:2',
        'calories' => 'integer',
        'protein' => 'decimal:2',
        'fat' => 'decimal:2',
        'carbs' => 'decimal:2',
        'fiber' => 'decimal:2',
        'sugar' => 'decimal:2',
        'is_custom' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function foodLogs()
{
    return $this->hasMany(FoodLog::class, 'food_id');
}
}