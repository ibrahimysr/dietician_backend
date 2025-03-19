<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use SoftDeletes;

    protected $table = 'recipes'; 

    protected $fillable = [
        'dietitian_id',
        'title',
        'description',
        'ingredients',
        'instructions',
        'prep_time',
        'cook_time',
        'servings',
        'calories',
        'protein',
        'fat',
        'carbs',
        'tags',
        'photo_url',
        'is_public',
    ];

    protected $casts = [
        'prep_time' => 'integer',
        'cook_time' => 'integer',
        'servings' => 'integer',
        'calories' => 'integer',
        'protein' => 'decimal:2',
        'fat' => 'decimal:2',
        'carbs' => 'decimal:2',
        'ingredients' => 'array', 
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

   
    public function dietitian()
    {
        return $this->belongsTo(Dietitian::class, 'dietitian_id');
    }
}