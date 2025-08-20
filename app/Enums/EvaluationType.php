<?php

namespace App\Enums;

enum EvaluationType: int
{
    case QUIZ  = 1; // Cuestionario
    case VIDEO = 2; // Evaluación por Video

    public function label(): string
    {
        return match ($this) {
            self::QUIZ  => 'Cuestionario',
            self::VIDEO => 'Video',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }
}
