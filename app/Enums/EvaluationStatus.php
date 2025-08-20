<?php

namespace App\Enums;

enum EvaluationStatus: string
{
    case SEND        = 'SEND';
    case REVISION    = 'REVISION';
    case APROVEED    = 'APROVEED';
    case NO_APROVEED = 'NO APROVEED';

    public function label(): string
    {
        return match ($this) {
            self::SEND        => 'Enviado',
            self::REVISION    => 'En revisión',
            self::APROVEED    => 'Aprobado',
            self::NO_APROVEED => 'No aprobado',
        };
    }

    /** Útil para selects en el frontend */
    public static function options(): array
    {
        return array_map(
            fn(self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        );
    }
}
