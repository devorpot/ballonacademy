<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ExtraClass extends Model
{
    protected $table = 'extra_classes';

    protected $fillable = [
        'title',
        'extract',
        'description',
        'image',
        'cover',
        'is_youtube',
        'youtube_url',
        'video',
        'subt_str',
        'category',
        'tags',
        'active',
        'order',
    ];

    protected $casts = [
        'is_youtube' => 'integer',  // 1 o 2
        'active'     => 'integer',  // 1 o 2
        'order'      => 'integer',
    ];
 
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover ? Storage::url($this->cover) : null;
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video ? Storage::url($this->video) : null;
    }

    public function getSubtUrlAttribute(): ?string
    {
        return $this->subt_str ? Storage::url($this->subt_str) : null;
    }

    // Scopes útiles
    public function scopeActive($q)
    {
        return $q->where('active', 1);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('order')->orderByDesc('id');
    }
}
