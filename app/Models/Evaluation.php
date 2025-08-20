<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Enums\EvaluationStatus;
use App\Enums\EvaluationType;       // para eva_type (p. ej., 1=Cuestionario, 2=Video)
use App\Enums\EvaluationsTypes;     // para type (1=Course, 2=Video)

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',        // incluye si haces asignación masiva con user_id
        'course_id',
        'teacher_id',
        'video_id',
        'eva_send_date',
        'eva_video_file',
        'eva_comments',
        'eva_type',       
        'status',
        'date_evaluation',
        'title',
        'type',
        'points_min',           
    ];

    protected $casts = [
        'status'          => EvaluationStatus::class,
        'eva_type'        => EvaluationType::class,     // enum existente para eva_type
        'type'            => EvaluationsTypes::class,   // enum nuevo para type
        'eva_send_date'   => 'date:Y-m-d',
        'date_evaluation' => 'date:Y-m-d',
    ];

     // Agrega estos campos calculados al JSON que consume Vue
    protected $appends = [
        'type_label',
        'type_name',
        'eva_type_label',
        'eva_type_name',
        'status_label',
        'status_name', 
    ];

    // ---- Accessors para TYPE (Course|Video) ----
    public function getTypeLabelAttribute(): ?string
    {
        // Si tu enum tiene método label()
        return $this->type?->label() ?? $this->type?->name ?? null;
    }

    public function getTypeNameAttribute(): ?string
    {
        return $this->type?->name; // p. ej. "COURSE" | "VIDEO"
    }

    // ---- Accessors para EVA_TYPE (Cuestionario|Video) ----
    public function getEvaTypeLabelAttribute(): ?string
    {
        return $this->eva_type?->label() ?? $this->eva_type?->name ?? null;
    }

    public function getEvaTypeNameAttribute(): ?string
    {
        return $this->eva_type?->name;
    }

    // ---- Accessors para STATUS ----
    public function getStatusLabelAttribute(): ?string
    {
        return $this->status?->label() ?? $this->status?->name ?? null;
    }

    public function getStatusNameAttribute(): ?string
    {
        return $this->status?->name;
    }

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
        return $this->hasMany(EvaluationQuestion::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }



    public function submissions() // entregas (EvaluationUser)
    {
        return $this->hasMany(\App\Models\EvaluationUser::class, 'evaluation_id');
    }

    // App\Models\EvaluationUser.php

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', \App\Enums\EvaluationUserStatus::APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', \App\Enums\EvaluationUserStatus::SEND); // o el que uses como “enviada”
    }




}
