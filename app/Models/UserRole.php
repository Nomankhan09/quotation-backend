<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $connection = 'tenant';
    protected $table = 'user_roles';
    protected $primaryKey = 'id';
    protected $fillable = [
        'role',
        'permission'
    ];
    public $timestamps = true;
}
