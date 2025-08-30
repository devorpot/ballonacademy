<?php 

// app/Models/VideoMaterial.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class VideoMaterial extends Model
{
    use HasFactory;

    protected $table = 'videos_materials';

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

    protected $casts = [
        'quantity' => 'float',
        'price'    => 'float',
    ];

    protected $appends = [
        'image_url',
        'total_cost',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    // URL pública de la imagen si existe
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    // Costo total = cantidad * precio
    public function getTotalCostAttribute()
    {
        $q = is_numeric($this->quantity) ? (float) $this->quantity : 0.0;
        $p = is_numeric($this->price) ? (float) $this->price : 0.0;
        return round($q * $p, 2);
    }

    // Scope auxiliar por video
    public function scopeForVideo($query, int $videoId)
    {
        return $query->where('video_id', $videoId);
    }
}
