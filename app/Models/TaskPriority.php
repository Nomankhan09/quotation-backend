<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskPriority extends Model
{
    protected $connection = 'tenant';
    protected $table = 'task_priority';
    protected $primaryKey = 'id';
    protected $fillable = [
        'priority',
        'color',
        'icon'
    ];
    public $timestamps = true;
}
