<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Progress extends Model
{
    use SoftDeletes;

    protected $table = 'progress'; 

    protected $fillable = [
        'client_id',
        'date',
        'weight',
        'waist',
        'arm',
        'chest',
        'hip',
        'body_fat_percentage',
        'notes',
        'photo_url',
    ];

    protected $casts = [
        'date' => 'date',
        'weight' => 'decimal:2',
        'waist' => 'decimal:2',
        'arm' => 'decimal:2',
        'chest' => 'decimal:2',
        'hip' => 'decimal:2',
        'body_fat_percentage' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}