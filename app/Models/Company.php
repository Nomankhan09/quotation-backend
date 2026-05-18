<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'tenant';
    protected $table = 'company';
    protected $primaryKey = 'id';
    protected $fillable = [
        'company_name',
        'company_address',
        'zip_code',
        'gst_number',
        'company_email',
        'company_phone',
        'website',
        'company_logo',
        'company_type',
        'pdf_file_name_format',
        'bank_name',
        'account_number',
        'ifsc_code',
        'account_holder_name',
        'swift_code',
    ];
    public $timestamps = true;
}
