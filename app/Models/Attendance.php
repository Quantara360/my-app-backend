<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'worksite_id',
        'shift',
        'date',
        'marked_at',
        'status',
        'method',
        'confidence',
    ];

    protected $casts = [
        'date'      => 'date',
        'marked_at' => 'datetime',
        'confidence' => 'float',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function worksite()
    {
        return $this->belongsTo(Worksite::class);
    }
}
