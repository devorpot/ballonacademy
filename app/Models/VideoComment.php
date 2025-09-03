<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoComment extends Model
{
    use HasFactory;

    protected $fillable = [
         'user_id',
        'course_id',
        'video_id',
        'comment',
        'likes',
        'dislikes',
        'active',
        'reply_comment_id'
    ]; 

    // Relaciones
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
    public function replies()
    {
        return $this->hasMany(VideoComment::class, 'reply_comment_id');
    }

    public function parent()
    {
        return $this->belongsTo(VideoComment::class, 'reply_comment_id');
    }
}
