<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'teacher_id',
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
        'facebook',
        'instagram',
        'tiktok',
        'website',
        'profile_image',
        'cover_image'
    ];

       protected $appends = ['display_name'];
    /**
     * Relación: Un maestro pertenece a un usuario.
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
        return $this->hasMany(\App\Models\Lesson::class);
    }

      public function getDisplayNameAttribute(): string
    {
        $fromNames = trim(trim(($this->firstname ?? '').' '.($this->lastname ?? '')));
        $fromUser  = optional($this->user)->name ?? '';
        return $fromNames !== '' ? $fromNames : $fromUser;
    }
}
