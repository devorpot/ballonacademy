<?php

 
namespace App\Enums;

enum ActivityType: int
{
    case VIDEO_START = 1;
    case VIDEO_END = 2;
    case COURSE_START = 3;
    case COURSE_END = 4;
    case EVALUATION_SEND = 5;
    case EVALUATION_APPROVED = 6;
    case EVALUATION_DECLINED = 7;
    case EVALUATION_DELETE = 8;

    public function label(): string
    {
        return match ($this) {
            self::VIDEO_START => 'Comenzo el video',
            self::VIDEO_END => 'Finalizo el video',
            self::COURSE_START => 'Comenzo un curso',
            self::COURSE_END => 'Finalizo un curso',
            self::EVALUATION_SEND => 'Envio una evaluación',
            self::EVALUATION_APPROVED => 'Aprobo la evaluación',
            self::EVALUATION_DECLINED => 'No paso la evaluación',
            self::EVALUATION_DELETE => 'Evaluación Eliminada',
        };
    }
}
