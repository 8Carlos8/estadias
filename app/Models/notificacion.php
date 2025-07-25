<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'mensaje',
        'leida',
        'fecha_envio',
        'prioridad',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class); //Agregar el nombre del campo de la relación
    }
}
