<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lead_id',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total_amount',
        'status',
        'terms',
        'specifications',
        'stage',
    ];

    protected $casts = [
        'terms' => 'array',
        'specifications' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(QuotationProduct::class);
    }

    public function paymentTerms()
    {
        return $this->hasMany(
            QuotationPaymentTerm::class,
            'quotation_id'
        );
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
