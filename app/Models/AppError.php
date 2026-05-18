<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppError extends Model
{
    protected $connection = 'tenant';
    protected $fillable = [
        'user_id',
        'message',
        'stack',
        'screen',
        'platform',
        'is_fatal',
        'app_version'
    ];
}
