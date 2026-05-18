<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $connection = 'mysql';
    protected $fillable = [
        'name', 'price',
        'max_users', 'max_quotations',
        'features', 'is_active'
    ];

    protected $casts = [
        'features'  => 'array',
        'is_active' => 'boolean',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}