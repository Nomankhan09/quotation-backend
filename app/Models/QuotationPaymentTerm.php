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
        'value',
        'payment_term_id'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(
            PaymentTerm::class,
            'payment_term_id'
        );
    }
}
