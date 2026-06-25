<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Worksite;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'assigned_worksite_id',
        'phone',
        'status',
        'nic',
        'age',
        'join_date',
        'face_recognition_enabled',
        'face_photo_path',
        'epf',
        'gender',
    ];

    protected $casts = [
        'age' => 'integer',
        'join_date' => 'date',
        'face_recognition_enabled' => 'boolean',
    ];

    public function worksite()
    {
        return $this->belongsTo(Worksite::class, 'assigned_worksite_id');
    }

    public function salaries()
    {
        return $this->hasMany(WorkerSalary::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function epfHistories()
    {
        return $this->hasMany(WorkerEpfHistory::class);
    }
}
