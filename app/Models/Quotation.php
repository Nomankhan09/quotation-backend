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
        'terms'
    ];

    protected $casts = [
        'terms' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(QuotationProduct::class);
    }

    public function paymentTerms()
    {
        return $this->hasMany(QuotationPaymentTerm::class);
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
