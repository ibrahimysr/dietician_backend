<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
    use SoftDeletes;

    protected $table = 'goals';

    protected $fillable = [
        'client_id',
        'dietitian_id',
        'title',
        'description',
        'target_value',
        'current_value',
        'unit',
        'category',
        'start_date',
        'target_date',
        'status',
        'priority',
        'progress_percentage',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'start_date' => 'date',
        'target_date' => 'date',
        'progress_percentage' => 'decimal:2',
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
}