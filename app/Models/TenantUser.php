<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUser extends Model
{
    protected $table = 'tenant_users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tenant_id',
        'email'
    ];
    protected $connection = 'mysql';
}
