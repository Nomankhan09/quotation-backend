<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicUser extends Model
{
    protected $connection = 'mysql';
    protected $table = 'public_users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'message',
        'status',
    ];
}
