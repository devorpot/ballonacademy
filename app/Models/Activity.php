<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
 
class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'video_id',
        'evaluation_id',
        'type',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }
}
