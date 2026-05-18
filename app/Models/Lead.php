<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $connection = 'tenant';
    protected $fillable = [
        'user_id',
        'full_name',
        'company_name',
        'email',
        'phone',
        'notes',
        'stage',
        'job_title',
        'location',
        'source',
        'profile_image'
    ];
    protected $appends = ['profile_image_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image
            ? url($this->profile_image)
            : null;
    }
}
