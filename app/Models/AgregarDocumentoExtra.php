<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgregarDocumentoExtra extends Model
{
    use HasFactory;

    protected $table = "documento_extras";

    protected $fillable = [
        'estadia_id',
        'nombre',
        'ruta',
        'fecha_subida',
    ];

    public function estadia()
    {
        return $this->belongsTo(Estadia::class, 'estadia_id');
    }
}
