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
        'day_shift_start',
        'day_shift_end',
        'day_late_grace_minutes',
        'day_early_grace_minutes',
        'night_shift_start',
        'night_shift_end',
        'night_late_grace_minutes',
        'night_early_grace_minutes',
    ];

    // Times are stored as MySQL TIME columns (e.g. "07:00:00") - cast to a
    // plain "H:i" string so the frontend gets "07:00" instead of Carbon's
    // default datetime serialization of a bare TIME value.
    protected $casts = [
        'day_shift_start'   => 'datetime:H:i',
        'day_shift_end'     => 'datetime:H:i',
        'night_shift_start' => 'datetime:H:i',
        'night_shift_end'   => 'datetime:H:i',
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
