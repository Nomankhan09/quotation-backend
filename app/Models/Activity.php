<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activity';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'lead_id',
        'title',
        'type',
        'date',
        'notes',
    ];

    public $timestamps = true;
}
