<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUps extends Model
{
    protected $connection = 'tenant';
    protected $table = 'follow_ups';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'contact_id',
        'title',
        'type',
        'date',
        'notes',
        'status',
        'notification_id'
    ];
    public $timestamps = true;
}
