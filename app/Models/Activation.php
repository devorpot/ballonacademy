<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activation extends Model
{
    protected $table = 'activations';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'size',
        'street',
        'number_int',
        'neighborhood',
        'postal_code',
        'city',
        'country',
        'state',
        'facebook',
        'tiktok',
        'instagram',
        'occupation',
        'experience',
        'has_business',
        'business_name',
        'business_type',
        'password_hash',
        'course_id',
        'code',
        'hash',
        'active',
        'created',
    ];

    // Tu tabla sí tiene created_at/updated_at, así que puedes dejar true
    public $timestamps = true;

    protected $casts = [
        'active'       => 'boolean',
        'created'      => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
