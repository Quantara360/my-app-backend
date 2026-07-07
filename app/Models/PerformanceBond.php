<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceBond extends Model
{
    use HasFactory;

    protected $fillable = [
        'valid_period',
        'date',
        'description',
        'amount',
        'tender_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
