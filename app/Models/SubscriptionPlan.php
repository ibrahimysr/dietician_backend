<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $table = 'subscription_plans'; 

    protected $fillable = [
        'dietitian_id',
        'name',
        'description',
        'duration',
        'price',
        'features',
        'status',
    ];

    protected $casts = [
        'duration' => 'integer',
        'price' => 'decimal:2',
        'features' => 'array', 
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function dietitian()
    {
        return $this->belongsTo(Dietitian::class, 'dietitian_id');
    }
}