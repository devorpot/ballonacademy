<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        // Datos básicos
        'firstname',
        'lastname',
        'phone',
        'specialty',
        'address',
        'country',
        'degree',
        'experience_years',
        'birth_date',
        'status',

        // Redes / sitio
        'facebook',
        'instagram',
        'tiktok',
        'website',
        'youtube',

        // Imágenes
        'profile_image',
        'cover_image',
    ];

    protected $appends = ['display_name'];

    /**
     * Relación: un maestro pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'master_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Accessor: nombre completo o fallback al nombre de usuario.
     */
    public function getDisplayNameAttribute(): string
    {
        $fromNames = trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? ''));
        $fromUser  = optional($this->user)->name ?? '';
        return $fromNames !== '' ? $fromNames : $fromUser;
    }
}
