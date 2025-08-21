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
        'course_id',
        'code',
        'hash',
        'active',
        'created',
        'activated_at', // si existe este campo en la tabla
    ];

    /**
     * Deshabilitamos los timestamps por defecto de Laravel,
     * ya que la tabla no usa created_at ni updated_at.
     */
    public $timestamps = false;

    protected $casts = [
        'created'      => 'datetime',
        'activated_at' => 'datetime',
        'active'       => 'boolean',
    ];

    /**
     * Relación con cursos.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
