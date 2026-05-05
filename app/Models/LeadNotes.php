<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadNotes extends Model
{
    protected $table = 'notes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'lead_id',
        'user_id',
        'note',
    ];
    public $timestamps = true;
}
