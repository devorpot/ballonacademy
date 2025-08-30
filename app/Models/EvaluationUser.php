<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Enums\EvaluationUserStatus;


class EvaluationUser extends Model
{
    use HasFactory;

    protected $table = 'evaluations_users';

    protected $fillable = [
        'user_id',
        'course_id',
        'video_id',
        'comments',
        
        'status',
        'files',
        'data',
        'evaluation_id',
        'approved_user_id',
    ];

    protected $casts = [
        'data' => 'array',
        'status' => EvaluationUserStatus::class,
        // otros casts...
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

    public function approvedUser()
    {
        return $this->belongsTo(User::class, 'approved_user_id');
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function getStatusLabelAttribute()
    {
        return $this->status?->label();
    }
}



