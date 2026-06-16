<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorksiteReport extends Model
{
    protected $fillable = [
        'worksite_id',
        'workers_count',
        'report_date',
        'image_1',
        'image_2',
        'image_3',
    ];

    public function worksite()
    {
        return $this->belongsTo(Worksite::class);
    }
}
