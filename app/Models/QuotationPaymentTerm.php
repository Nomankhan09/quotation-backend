<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationPaymentTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'description',
        'value'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
