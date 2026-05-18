<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationProduct extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $fillable = [
        'quotation_id',
        'product_id',
        'length',
        'width',
        'unit',
        'unit_price',
        'quantity',
        'total_price'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}