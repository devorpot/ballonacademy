<?php 

// app/Models/VideoMaterial.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoMaterial extends Model
{
    use HasFactory;

    protected $table = 'videos_materials'; // Forzar el nombre exacto de la tabla

    protected $fillable = [
        'video_id',
        'name',
        'quantity',
        'unit',
        'notes',
        'image',
        'price',
        'buy_link',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
