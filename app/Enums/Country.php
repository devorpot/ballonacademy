<?php

namespace App\Enums;

enum Country: string
{
    case MX = 'MX';
    case AR = 'AR';
    case CL = 'CL';
    case CO = 'CO';
    case PE = 'PE';
    // añade los que uses en LATAM...

    public function label(): string
    {
        return match($this) {
            self::MX => 'México',
            self::AR => 'Argentina',
            self::CL => 'Chile',
            self::CO => 'Colombia',
            self::PE => 'Perú',
        };
    }

    /** Para selects: [['value'=>'MX','label'=>'México'], ...] */
    public static function options(): array
    {
        return array_map(fn($c) => ['value'=>$c->value, 'label'=>$c->label()], self::cases());
    }
}
