<?php
// app/Enums/EvaluationType.php
namespace App\Enums;

enum EvaluationType: int
{
    case QUESTIONNAIRE = 1;
    case VIDEO = 2;

    public function label(): string
    {
        return match ($this) {
            self::QUESTIONNAIRE => 'Cuestionario',
            self::VIDEO => 'Video',
        };
    }
}
