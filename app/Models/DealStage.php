<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealStage extends Model
{
    protected $connection = 'tenant';
    protected $table = 'deal_stages';
    protected $primaryKey = 'id';
    protected $fillable = [
        'stage_name',
        'probability',
        'is_closed',
        'is_won',
        'color',
    ];
    public $timestamps = true;
}
