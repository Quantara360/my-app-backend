<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'location',
        'status',
        'assigned_to',
        'count',
        'value',
        'worksite_id',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function worksite()
    {
        return $this->belongsTo(Worksite::class, 'worksite_id');
    }

    protected $casts = [
        'count' => 'integer',
        'value' => 'decimal:2',
    ];
}
