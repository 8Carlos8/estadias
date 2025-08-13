<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartaTerminacion extends Model
{
    use HasFactory;

    protected $table = "carta_terminacions";

    protected $fillable = [
        'estadia_id',
        'tutor_id',
        'fecha_subida',
        'documento',
    ];

    public function estadia()
    {
        return $this->belongsTo(Estadia::class, 'estadia_id');
    }

    public function tutor()
    {
        return $this->belongsTo(Estadia::class, 'tutor_id');
    }
}
