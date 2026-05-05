<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $table = 'deals';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'stage_id',
        'lead_id',
        'title',
        'value',
        'expected_close_date',
        'assigned_to',
        'description',
    ];
    public $timestamps = true;
}
