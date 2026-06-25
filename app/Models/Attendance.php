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
        'sub_site_id',
        'shift',
        'date',
        'marked_at',
        'status',
        'method',
        'confidence',
        'out_marked_at',
        'out_method',
        'out_confidence',
    ];

    protected $casts = [
        'date'           => 'date',
        'marked_at'      => 'datetime',
        'out_marked_at'  => 'datetime',
        'confidence'     => 'float',
        'out_confidence' => 'float',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function worksite()
    {
        return $this->belongsTo(Worksite::class);
    }

    public function subSite()
    {
        return $this->belongsTo(\App\Models\SubSite::class, 'sub_site_id');
    }
}
