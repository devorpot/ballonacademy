<?php

namespace App\Enums;

enum EvaluationUserStatus: int
{
    case SEND = 111;
    case REVISION = 222;
    case FAILED = 333;
    case SUCCESS = 999;

    public function label(): string
    {
        return match($this) {
            self::SEND => 'Enviado',
            self::REVISION => 'En revisión',
            self::FAILED => 'No aprobado',
            self::SUCCESS => 'Aprobado',
        };
    }

    // Si quieres obtener el color de badge
    public function color(): string
    {
        return match($this) {
            self::SEND => 'secondary',
            self::REVISION => 'info',
            self::FAILED => 'danger',
            self::SUCCESS => 'success',
        };
    }
}
