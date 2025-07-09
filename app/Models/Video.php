<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'video_url',
    ];

    public function course()
{
    return $this->belongsTo(Course::class);
}

public function teacher()
{
    return $this->belongsTo(Teacher::class);
}


}
