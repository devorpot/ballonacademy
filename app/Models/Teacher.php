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
        'status'
    ];

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
}
