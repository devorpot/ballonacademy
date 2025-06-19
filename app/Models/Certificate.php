<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'master_id',
        'authorized_by',
        'date_start',
        'date_end',
        'date_expedition',
        'comments',
        'photo',
        'logo'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function master()
    {
        return $this->belongsTo(Teacher::class, 'master_id');
    }
}
