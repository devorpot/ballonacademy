<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evaluation;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'description_short',
        'teacher_id',
        'comments',
        'image_cover',
 
        'video_path',
        'order',
        'duration',
        'size'
    ];

        public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

        public function evaluations()
    {
        return $this->hasMany(Evaluation::class); // FK: evaluations.video_id
    }

    public function resources()
    {
        return $this->hasMany(VideoResource::class);
    }

    public function captions()
    {
        return $this->hasMany(VideoCaption::class);
    }


}
