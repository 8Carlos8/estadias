<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estadia_seguimiento extends Model
{
    use HasFactory;

    protected $table = "estadia_seguimientos";

    protected $fillable = [
        'estadia_id',
        'etapa',
        'estatus',
        'comentario',
        'fecha_actualizacion',
        'actualizado_por',
    ];

    public function estadia()
    {
        return $this->belongsTo(Estadia::class, 'estadia_id');
    }

    //Llave para los tutores ocupar la tabla o la api para que muestre que tutor
    public function actualPor() 
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }
}
