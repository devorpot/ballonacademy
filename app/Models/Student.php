<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'firstname',
        'lastname',
        'phone',
        'shirt_size',
        'address',
        'country',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
