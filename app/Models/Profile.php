<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'rfc',
    'business_name',
    'street',
    'external_number',
    'internal_number',
    'state',
    'municipality',
    'neighborhood',
    'postal_code',
    'billing_email',
    'tax_regime',
    'cfdi_use',
    'personal_email',
    'country',
    'whatsapp',
    'nickname',
    'profile_image',
    'cover_image',
    'website',
    'facebook',
    'instagram',
    'tiktok',
    'youtube',
    'description',
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
