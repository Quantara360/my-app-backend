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

    // Deliberately NOT cast to 'datetime' - a datetime:H:i cast returns a
    // Carbon instance from getAttribute(), which only gets formatted to
    // "H:i" by Eloquent's own toArray()/toJson() on the model itself. Any
    // other route (only(), a plain array built manually and passed to
    // json_encode/Response::json, string interpolation, etc.) sees Carbon's
    // own default __toString()/jsonSerialize() instead - a full ISO
    // datetime, not "H:i" - which is exactly the shape OfficeController
    // reads these in. Left uncast, these MySQL TIME columns come back as
    // plain "H:i:s" strings from the DB driver with no cast surprises;
    // resolveShiftConfig() truncates to "H:i" itself.

    public function worksite()
    {
        return $this->belongsTo(Worksite::class, 'worksite_id');
    }

    public function subSites()
    {
        return $this->hasMany(SubSite::class, 'hospital_id');
    }
}
