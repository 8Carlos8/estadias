<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificacionMFA extends Model
{
    protected $table = 'verificaciones_mfa';

    protected $fillable = [
        'usuario_id',
        'codigo_enviado',
        'metodo',
        'valido_hasta',
        'verificado',
    ];

    protected $casts = [
        'verificado' => 'boolean',
        'valido_hasta' => 'datetime',
    ];

    // Relación 
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}

