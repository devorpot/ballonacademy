<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'description',
        'country',
        'state',
        'region',
        'zone',
        'address',
        'gmap_link',
        'lat',
        'lng',
        'email',
        'whatsapp',
        'phone',
        'website',
        'facebook',
        'instagram',
        'tiktok',
    ];
}
