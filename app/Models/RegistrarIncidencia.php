<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrarIncidencia extends Model
{
    use HasFactory;

    protected $table = "incidencias";

    protected $fillable = [
        'estadia_id',
        'descripcion',
        'fecha',
    ];

    public function estadia()
    {
        return $this->belongsTo(Estadia::class, 'estadia_id');
    }
}
