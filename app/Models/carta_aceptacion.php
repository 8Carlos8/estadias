<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class carta_aceptacion extends Model
{
    protected $table = 'carta_aceptacions';

     protected $fillable = [
        'estadia_id',
        'fecha_recepcion',
        'ruta_documento',
        'observaciones',
    ];

    //  Relación
    public function estadia()
    {
        return $this->belongsTo(Estadia::class, 'estadia_id');
    }
}

