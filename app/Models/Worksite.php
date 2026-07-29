<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worksite extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'supervisor_id',
        'parent_id',
        'type',
        'logo',
    ];

    public function hospitals()
    {
        return $this->hasMany(Hospital::class, 'worksite_id');
    }
}
