<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class verificacion_mfa extends Model
{
    use HasFactory;

    protected $table = 'verificacion_mfas';

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
        return $this->belongsTo(User::class, 'usuario_id');
    }
}

