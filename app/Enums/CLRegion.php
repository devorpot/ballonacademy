<?php

namespace App\Enums;

enum CLRegion: string
{
    case ARICA_PARINACOTA = 'Arica y Parinacota';
    case TARAPACA = 'Tarapacá';
    case ANTOFAGASTA = 'Antofagasta';
    case ATACAMA = 'Atacama';
    case COQUIMBO = 'Coquimbo';
    case VALPARAISO = 'Valparaíso';
    case METROPOLITANA = 'Región Metropolitana de Santiago';
    case O_HIGGINS = 'Libertador General Bernardo O’Higgins';
    case MAULE = 'Maule';
    case NUBLE = 'Ñuble';
    case BIOBIO = 'Biobío';
    case ARAUCANIA = 'La Araucanía';
    case LOS_RIOS = 'Los Ríos';
    case LOS_LAGOS = 'Los Lagos';
    case AYSEN = 'Aysén del General Carlos Ibáñez del Campo';
    case MAGALLANES = 'Magallanes y de la Antártica Chilena';

    public static function options(): array
    {
        return array_map(fn($c) => ['value'=>$c->value, 'label'=>$c->value], self::cases());
    }
}
