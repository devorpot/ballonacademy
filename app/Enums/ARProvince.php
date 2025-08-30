<?php

namespace App\Enums;

enum ARProvince: string
{
    case BUENOS_AIRES = 'Buenos Aires';
    case CABA = 'Ciudad Autónoma de Buenos Aires';
    case CATAMARCA = 'Catamarca';
    case CHACO = 'Chaco';
    case CHUBUT = 'Chubut';
    case CORDOBA = 'Córdoba';
    case CORRIENTES = 'Corrientes';
    case ENTRE_RIOS = 'Entre Ríos';
    case FORMOSA = 'Formosa';
    case JUJUY = 'Jujuy';
    case LA_PAMPA = 'La Pampa';
    case LA_RIOJA = 'La Rioja';
    case MENDOZA = 'Mendoza';
    case MISIONES = 'Misiones';
    case NEUQUEN = 'Neuquén';
    case RIO_NEGRO = 'Río Negro';
    case SALTA = 'Salta';
    case SAN_JUAN = 'San Juan';
    case SAN_LUIS = 'San Luis';
    case SANTA_CRUZ = 'Santa Cruz';
    case SANTA_FE = 'Santa Fe';
    case SANTIAGO_DEL_ESTERO = 'Santiago del Estero';
    case TIERRA_DEL_FUEGO = 'Tierra del Fuego, Antártida e Islas del Atlántico Sur';
    case TUCUMAN = 'Tucumán';

    public static function options(): array
    {
        return array_map(fn($c) => ['value'=>$c->value, 'label'=>$c->value], self::cases());
    }
}
