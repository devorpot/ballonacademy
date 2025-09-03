<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseActivity extends Model
{
    protected $fillable = [
        'course_id', 'user_id', 'activity', 'ended', 'activity_date',
    ];

    protected $casts = [
        'ended' => 'boolean',
        'activity_date' => 'datetime',
    ];

    // Relaciones
    public function course()
    {
        // asume clave foránea course_id y PK id
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        // asume clave foránea user_id y PK id
        return $this->belongsTo(User::class);
    }
}
