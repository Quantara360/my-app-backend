<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubSiteImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_site_id',
        'book_id',
        'image_path',
    ];

    public function subSite()
    {
        return $this->belongsTo(SubSite::class);
    }
}
