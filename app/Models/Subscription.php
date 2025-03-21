<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;

    protected $table = 'subscriptions';

    protected $fillable = [
        'client_id',
        'dietitian_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'status',
        'auto_renew',
        'payment_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
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

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
    public function payments()
{
    return $this->hasMany(Payment::class, 'subscription_id');
}
}