<?php 


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order',
        'active',
        'title',
        'instructions',
        'description_short',
        'publish_on',
        'course_id',
        'teacher_id',
        'add_video',
        'add_evaluation',
        'add_materials',
        'videos',
        'evaluations',
        'image',
        'image_cover',
        'materials',
    ];

    protected $casts = [
        'active'          => 'boolean',
        'publish_on'      => 'date',
        'add_video'       => 'boolean',
        'add_evaluation'  => 'boolean',
        'add_materials'   => 'boolean',
        'videos'          => 'array',
        'evaluations'     => 'array',
        'materials'       => 'array', 
    ];

    // Relaciones
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Scopes útiles
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopePublished($query)
     {
        return $query->whereDate('publish_on', '<=', now()->toDateString());
    }

    public function lessonVideos()
    {
        return $this->hasMany(LessonVideo::class);
    }

   public function videos()
    {
        return $this->belongsToMany(\App\Models\Video::class, 'lesson_videos')
            ->withPivot(['course_id', 'order', 'active'])
            ->withTimestamps()
            ->orderBy('lesson_videos.order'); // usa la columna real del pivot
    }


    public function assignedVideos()
{
    return $this->belongsToMany(\App\Models\Video::class, 'lesson_videos')
        ->withPivot(['course_id', 'order', 'active'])
        ->withTimestamps()
        ->orderBy('lesson_videos.order');
}

// Pivot como colección explícita
public function lessonEvaluations()
{
    return $this->hasMany(\App\Models\LessonEvaluation::class);
}

// Acceso directo a evaluaciones con datos del pivot
public function assignedEvaluations()
{
    return $this->belongsToMany(\App\Models\Evaluation::class, 'lesson_evaluations')
        ->withPivot(['order', 'active'])
        ->withTimestamps()
        ->orderBy('lesson_evaluations.order');
}

public function evaluations()
{
    // Alias simple sin orderBy por si lo necesitas "en crudo"
    return $this->belongsToMany(\App\Models\Evaluation::class, 'lesson_evaluations')
        ->withPivot(['order', 'active'])
        ->withTimestamps();
}

    
}