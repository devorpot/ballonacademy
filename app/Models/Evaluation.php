<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Enums\EvaluationStatus;
use App\Enums\EvaluationType;

class Evaluation extends Model
{
    use HasFactory;

   
    protected $fillable = [
        'user_id',
        'course_id',
        'teacher_id',
        'eva_send_date',
        'eva_video_file',
        'eva_comments',
        'eva_type',       
        'status',
        'date_evaluation',
    ];

    protected $casts = [
        'status' => EvaluationStatus::class,
        'eva_type' => EvaluationType::class,  
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
       public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\EvaluationQuestion::class);
    }
}
