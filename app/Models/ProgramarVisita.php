<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramarVisita extends Model
{
    use HasFactory;

    protected $table = "visitas";

    protected $fillable = [
        'estadia_id',
        'user_id',
        'fecha',
        'hora',
    ];

    public function estadia()
    {
        return $this->belongsTo(Estadia::class, 'estadia_id');
    }

    //Llave para los tutores ocupar la tabla o la api para que muestre que tutor
    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
