<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dietitian extends Model
{
    use SoftDeletes;

    protected $table = 'dietitians';

    protected $fillable = [
        'user_id',
        'specialty',
        'bio',
        'hourly_rate',
        'experience_years',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function clients()
    {
        return $this->hasMany(Client::class, 'dietitian_id');
    }
}