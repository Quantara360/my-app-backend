<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'department',
        'email',
        'phone',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function salaries()
    {
        return $this->hasMany(OfficeStaffSalary::class);
    }
}
