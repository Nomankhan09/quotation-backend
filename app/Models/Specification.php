<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
    protected $connection = 'tenant';
    protected $table = 'specifications';
    protected $primaryKey = 'id';
    protected $fillable = [
        'item',
        'description',
        'user_id',
    ];
    public $timestamps = true;

    protected $casts = [
        'description' => 'array',
    ];
}
