<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'requested_by',
        'approved_by',
        'worksite_id',
        'amount',
        'date',
        'holder',
    ];

    public function worksite()
    {
        return $this->belongsTo(Worksite::class, 'worksite_id');
    }
}
