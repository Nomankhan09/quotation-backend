<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $connection = 'tenant';
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

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function quotation()
    {
        return $this->hasMany(Quotation::class, 'deal_id')->latest();
    }

    public function stage()
    {
        return $this->belongsTo(DealStage::class);
    }
}
