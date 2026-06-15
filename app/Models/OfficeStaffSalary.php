<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeStaffSalary extends Model
{
    use HasFactory;

    protected $table = 'office_staff_salaries';

    protected $fillable = [
        'name',
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
        'salary' => 'decimal:2',
        'date' => 'date',
    ];
}
