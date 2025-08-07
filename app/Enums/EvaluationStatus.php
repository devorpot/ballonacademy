<?php

namespace App\Enums;

enum EvaluationStatus: string
{
    case SEND = 'SEND';
    case REVISION = 'REVISION';
    case APROVEED = 'APROVEED';
    case NO_APROVEED = 'NO APROVEED';

    public function label(): string
    {
        return match ($this) {
            self::SEND => 'Enviado',
            self::REVISION => 'En revisión',
            self::APROVEED => 'Aprobado',
            self::NO_APROVEED => 'No aprobado',
        };
    }
}
