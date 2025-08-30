<?php

namespace App\Enums;

enum CODepartment: string
{
    case AMAZONAS = 'Amazonas';
    case ANTIOQUIA = 'Antioquia';
    case ARAUCA = 'Arauca';
    case ATLANTICO = 'Atlántico';
    case BOLIVAR = 'Bolívar';
    case BOYACA = 'Boyacá';
    case CALDAS = 'Caldas';
    case CAQUETA = 'Caquetá';
    case CASANARE = 'Casanare';
    case CAUCA = 'Cauca';
    case CESAR = 'Cesar';
    case CHOCO = 'Chocó';
    case CORDOBA = 'Córdoba';
    case CUNDINAMARCA = 'Cundinamarca';
    case GUAINIA = 'Guainía';
    case GUAVIARE = 'Guaviare';
    case HUILA = 'Huila';
    case LA_GUAJIRA = 'La Guajira';
    case MAGDALENA = 'Magdalena';
    case META = 'Meta';
    case NARINO = 'Nariño';
    case NORTE_DE_SANTANDER = 'Norte de Santander';
    case PUTUMAYO = 'Putumayo';
    case QUINDIO = 'Quindío';
    case RISARALDA = 'Risaralda';
    case SAN_ANDRES = 'Archipiélago de San Andrés, Providencia y Santa Catalina';
    case SANTANDER = 'Santander';
    case SUCRE = 'Sucre';
    case TOLIMA = 'Tolima';
    case VALLE_DEL_CAUCA = 'Valle del Cauca';
    case VAUPES = 'Vaupés';
    case VICHADA = 'Vichada';
    case BOGOTA_DC = 'Bogotá, D.C.';

    public static function options(): array
    {
        return array_map(fn($c) => ['value'=>$c->value, 'label'=>$c->value], self::cases());
    }
}
