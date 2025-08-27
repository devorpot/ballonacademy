<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonActivity extends Model
{
    use HasFactory;

    /**
     * Tabla asociada.
     */
    protected $table = 'lesson_activities';

    /**
     * Asignación masiva permitida.
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'activity',
        'ended',
        'activity_date',
    ];

    /**
     * Casts de atributos.
     */
    protected $casts = [
        'ended'         => 'boolean',
        'activity_date' => 'datetime',
    ];

    /**
     * Posibles valores para "activity".
     * Úsalos para evitar strings mágicos.
     */
    public const ACT_STARTED  = 'started';
    public const ACT_PROGRESS = 'progress';
    public const ACT_ENDED    = 'ended';

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Scopes de ayuda
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeForLesson($query, int $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    public function scopeBetween($query, $start, $end)
    {
        return $query->whereBetween('activity_date', [$start, $end]);
    }

    public function scopeEnded($query)
    {
        return $query->where('ended', true);
    }
}
