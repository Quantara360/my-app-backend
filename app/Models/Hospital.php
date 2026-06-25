<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'worksite_id',
    ];

    public function worksite()
    {
        return $this->belongsTo(Worksite::class, 'worksite_id');
    }

    public function subSites()
    {
        return $this->hasMany(SubSite::class, 'hospital_id');
    }
}
