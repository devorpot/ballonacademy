<?php

namespace App\Enums;

enum MXState: string
{
    case AGUASCALIENTES = 'Aguascalientes';
    case BAJA_CALIFORNIA = 'Baja California';
    case BAJA_CALIFORNIA_SUR = 'Baja California Sur';
    case CAMPECHE = 'Campeche';
    case CDMX = 'Ciudad de México';
    case CHIAPAS = 'Chiapas';
    case CHIHUAHUA = 'Chihuahua';
    case COAHUILA = 'Coahuila';
    case COLIMA = 'Colima';
    case DURANGO = 'Durango';
    case GUANAJUATO = 'Guanajuato';
    case GUERRERO = 'Guerrero';
    case HIDALGO = 'Hidalgo';
    case JALISCO = 'Jalisco';
    case MEXICO = 'México';
    case MICHOACAN = 'Michoacán';
    case MORELOS = 'Morelos';
    case NAYARIT = 'Nayarit';
    case NUEVO_LEON = 'Nuevo León';
    case OAXACA = 'Oaxaca';
    case PUEBLA = 'Puebla';
    case QUERETARO = 'Querétaro';
    case QUINTANA_ROO = 'Quintana Roo';
    case SAN_LUIS_POTOSI = 'San Luis Potosí';
    case SINALOA = 'Sinaloa';
    case SONORA = 'Sonora';
    case TABASCO = 'Tabasco';
    case TAMAULIPAS = 'Tamaulipas';
    case TLAXCALA = 'Tlaxcala';
    case VERACRUZ = 'Veracruz';
    case YUCATAN = 'Yucatán';
    case ZACATECAS = 'Zacatecas';

    public static function options(): array
    {
        return array_map(fn($s) => ['value'=>$s->value, 'label'=>$s->value], self::cases());
    }
}
