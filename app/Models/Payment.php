<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $table = 'payments'; 

    protected $fillable = [
        'subscription_id',
        'client_id',
        'amount',
        'currency',
        'payment_date',
        'payment_method',
        'transaction_id',
        'status',
        'refund_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'refund_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}