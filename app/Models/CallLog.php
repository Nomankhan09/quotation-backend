<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    protected $table = 'call_log';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'lead_id',
        'duration',
        'type',
        'date',
        'timestamp',
    ];
    public $timestamps = true;
}
