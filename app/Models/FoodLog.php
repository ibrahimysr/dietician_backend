<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodLog extends Model
{
    use SoftDeletes;

    protected $table = 'food_logs'; 

    protected $fillable = [
        'client_id',
        'food_id',
        'date',
        'meal_type',
        'food_description',
        'quantity',
        'calories',
        'protein',
        'fat',
        'carbs',
        'photo_url',
        'logged_at',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'decimal:2',
        'calories' => 'integer',
        'protein' => 'decimal:2',
        'fat' => 'decimal:2',
        'carbs' => 'decimal:2',
        'logged_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function food()
    {
        return $this->belongsTo(Food::class, 'food_id');
    }
}