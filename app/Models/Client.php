<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $table = 'clients'; 

    protected $fillable = [
        'user_id',
        'dietitian_id',
        'birth_date',
        'gender',
        'height',
        'weight',
        'activity_level',
        'goal',
        'allergies',
        'preferences',
        'medical_conditions',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dietitian()
    {
        return $this->belongsTo(Dietitian::class, 'dietitian_id');
    }
}