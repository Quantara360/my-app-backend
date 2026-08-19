<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubSiteImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_site_id',
        'worksite_id',
        'book_id',
        'uploaded_by',
        'image_path',
    ];

    // uploaded_by_name is derived from the uploadedBy relation so the
    // frontend doesn't have to do a second lookup just to label each photo
    // with who captured it.
    protected $appends = ['uploaded_by_name'];

    public function subSite()
    {
        return $this->belongsTo(SubSite::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUploadedByNameAttribute(): ?string
    {
        return $this->uploadedBy?->name;
    }
}
