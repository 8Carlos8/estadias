<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cartas_presentacion extends Model
{
    use HasFactory;

    protected $table = "cartas_presentacions";

    protected $fillable = [
        'estadia_id',
        'tutor_id',
        'fecha_emision',
        'ruta_documento',
        'firmada_director',
    ];

    public function estadia()
    {
        return $this->belongsTo(Estadia::class, 'estadia_id');
    }

    //Llave para los tutores ocupar la tabla o la api para que muestre que tutor
    public function tutor() 
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }
}
