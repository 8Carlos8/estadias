<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estadia extends Model
{
    use HasFactory;

    protected $table = "estadias";

    protected $fillable = [
        'alumno_id',
        'empresa',
        'asesor_externo',
        'proyecto_nombre',
        'duracion_semanas',
        'fecha_inicio',
        'fecha_fin',
        'apoyo',
        'estatus',
    ];

    //Checar la llave si va en alumno o usuario
    public function alumno()
    {
        return $this->belongsTo(User::class, 'alumno_id');
    }
    //Agregar la llave pa que se relacione con la empresa con la tabla de los datos dummy XD
}
