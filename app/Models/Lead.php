<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = ['user_id','full_name','company_name','email','phone','notes'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}

