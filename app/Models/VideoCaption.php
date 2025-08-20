<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoCaption extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'lang',
        'file',
        'captions',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
