<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chemical extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quantity',
        'hazard_level',
        'storage_location',
        'worksite_id',
    ];

    public function worksite()
    {
        return $this->belongsTo(Worksite::class, 'worksite_id');
    }
}
