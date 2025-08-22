<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonVideo extends Model
{
    use HasFactory;

    protected $table = 'lesson_videos';

    protected $fillable = [
        'lesson_id',
        'video_id',
        'course_id',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order'  => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query, bool $isActive = true)
    {
        return $query->where('active', $isActive);
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeOrdered($query)
    {
        // Usa el alias explícito por claridad
        return $query->orderBy($this->getTable().'.order');
    }
}
