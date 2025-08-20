<?php 

// app/Models/VideoResource.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'title',
        'description',
        'type',
        'file_src',
        'video_src',
        'img_src',
        'uploaded',
    ];

    protected $casts = [
        'uploaded' => 'date',
        'type' => 'integer',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
