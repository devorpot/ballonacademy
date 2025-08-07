<?php

// app/Models/EvaluationQuestion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluationQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'type',
        'order',
        'question',
        'option_1',
        'option_2',
        'option_3',
        'option_4',
        'option_5',
        'response_text',
        'response_option',
        'status',
        'options_json',
        'points'
    ];
    protected $casts = [
        'options_json' => 'array',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }
}
