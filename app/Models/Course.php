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
        'active',
        'price',
        'payment_link',
        'currency_id'
    ];

   public function videos()
{
    // belongsToMany usando course_id como FK "propia" en el pivote
    return $this->belongsToMany(Video::class, 'lesson_videos', 'course_id', 'video_id')
        ->withPivot(['lesson_id', 'order', 'active'])
        ->withTimestamps()
        ->orderBy('lesson_videos.order');
}
    
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function evaluations()
    {
        return $this->hasMany(\App\Models\Evaluation::class);
    }



    public function students()
        {
            return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id');
        }


    public function users()
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id');
    }

  
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function lessonVideos()
{
    return $this->hasMany(LessonVideo::class);
}



public function lessons()
{
    // Si quieres también las lecciones conectadas por pivote
   return $this->hasMany(\App\Models\Lesson::class);
}

}
