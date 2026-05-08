<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'email', 'phone',
        'db_name', 'status',
        'plan_id', 'trial_ends_at'
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isTrialExpired(): bool
    {
        return $this->status === 'trial'
            && now()->isAfter($this->trial_ends_at);
    }
}