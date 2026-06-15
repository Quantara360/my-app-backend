<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerSalary extends Model
{
    use HasFactory;

    protected $table = 'worker_salaries';

    protected $fillable = [
        'worker_id',
        'salary',
        'type',
        'date',
        'worksite_id',
    ];

    public function worksite()
    {
        return $this->belongsTo(Worksite::class, 'worksite_id');
    }

    protected $casts = [
        'worker_id' => 'integer',
        'salary' => 'decimal:2',
        'date' => 'date',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
