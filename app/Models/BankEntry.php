<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'cheque_no',
        'description',
        'debit',
        'credit',
        'balance',
        'linked_transfer_id',
    ];

    protected $casts = [
        'date'    => 'date:Y-m-d',
        'debit'   => 'float',
        'credit'  => 'float',
        'balance' => 'float',
    ];
}
