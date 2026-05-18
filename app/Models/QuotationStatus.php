<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationStatus extends Model
{
    protected $connection = 'tenant';
    protected $table = 'quotation_status';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'status',
    ];
    public $timestamps = true;
}
