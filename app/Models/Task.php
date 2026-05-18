<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $connection = 'tenant';
    protected $table = 'task';
    protected $primaryKey = 'id';
    protected $fillable = [
        'contact_id',
        'user_id',
        'title',
        'status',
        'due_date',
        'priority',
        'notes',
        'notification_id'
    ];
    public $timestamps = true;

    public function contact()
    {
        return $this->belongsTo(Lead::class, 'contact_id');
    }

    public function priority()
    {
        return $this->belongsTo(TaskPriority::class, 'priority');
    }
}
