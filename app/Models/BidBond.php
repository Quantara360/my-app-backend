<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidBond extends Model
{
    use HasFactory;

    protected $fillable = [
        'valid_period',
        'bond_name',
        'bond_number',
        'tender_status',
        'duration_date',
        'description',
        'amount',
        'is_awarded',
    ];

    protected $casts = [
        'is_awarded' => 'boolean',
        'amount' => 'decimal:2',
    ];
}
