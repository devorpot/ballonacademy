<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationFile extends Model
{
    use HasFactory;

    // La tabla no sigue la convención (evaluations_files)
    protected $table = 'evaluations_files';

    protected $fillable = [
        'evaluation_id',
        'order',
        'title',
        'description',
        'file_uploaded',
        'type',
        'size',
        'uploaded',
    ];

    protected $casts = [
        'uploaded' => 'date', // cambia a 'datetime' si prefieres fecha+hora
        'size'     => 'integer',
        'order'    => 'integer',
    ];

    // Relación: muchos archivos pertenecen a una evaluación
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    // (Opcional) Scope para ordenar por 'order'
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
