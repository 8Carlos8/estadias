<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verificacion_documento extends Model
{
    use HasFactory;

    protected $table = "verificacion_documentos";

    protected $fillable = [
        'usuario_id',
        'tipo_validacion',
        'resultado',
        'fecha_validacion',
        'observaciones',
    ];

    public function userID() 
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
