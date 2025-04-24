<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dietitian extends Model
{
    use SoftDeletes;
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';  
    protected $table = 'dietitians';

    protected $fillable = [
        'user_id',
        'specialty',
        'bio',
        'hourly_rate',
        'experience_years',
        'is_active',
        'status',      
        'rejection_reason', 
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ]; 

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'is_active' => false, 
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function clients()
    {
        return $this->hasMany(Client::class, 'dietitian_id');
    }
    public function dietPlans()
    {
        return $this->hasMany(DietPlan::class, 'dietitian_id');
    }
    public function subscriptionPlans()
    {
        return $this->hasMany(SubscriptionPlan::class, 'dietitian_id');
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'dietitian_id');
    }
    public function goals()
    {
        return $this->hasMany(Goal::class, 'dietitian_id');
    }
    public function recipes()
    {
        return $this->hasMany(Recipe::class, 'dietitian_id');
    } 

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeIsActive($query)
    {
        return $query->where('status', self::STATUS_APPROVED)->where('is_active', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
