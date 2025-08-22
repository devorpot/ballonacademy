<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonEvaluation extends Model
{
    use HasFactory;

    protected $table = 'lesson_evaluations';

    protected $fillable = [
        'lesson_id',
        'evaluation_id',
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

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
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
        return $query->orderBy($this->getTable() . '.order');
    }
}
