<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartaAceptacion extends Model
{
    protected $table = 'cartas_aceptacions';

    protected $fillable = [
        'estadia_id',
        'fecha_recepcion',
        'ruta_documento',
        'observaciones',
    ];

    // relación
    public function estadia()
    {
        return $this->belongsTo(Estadia::class); //Agregar el campo de relación
    }
}

