<?php

namespace App\Enums;

enum EvaluationsTypes: int
{
    case COURSE = 1; // Evaluación general del Curso
    case VIDEO  = 2; // Evaluación para el Video

    public function label(): string
    {
        return match ($this) {
            self::COURSE => 'Evaluación general del Curso',
            self::VIDEO  => 'Evaluación para el Video',
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
