<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerSetting extends Model
{
    protected $fillable = [
        'ledger_type',
        'manual_prev_balance',
    ];

    protected $casts = [
        'manual_prev_balance' => 'float',
    ];
}
