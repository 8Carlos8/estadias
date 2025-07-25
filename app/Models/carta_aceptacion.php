<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartaAceptacion extends Model
{
    protected $table = 'cartas_aceptacion';

    protected $fillable = [
        'estadia_id',
        'fecha_recepcion',
        'ruta_documento',
        'observaciones',
    ];

    // relación
    public function estadia()
    {
        return $this->belongsTo(Estadia::class);
    }
}

