<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'description_short',
        'level',
        'image_cover',
        'logo',
        'date_start',
        'date_end',
    ];

    public function videos()
    {
        return $this->hasMany(Video::class);
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }



      public function students()
{
    return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id');
}


public function users()
{
    return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id');
}

}
