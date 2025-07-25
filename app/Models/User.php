<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'curp',
        'correo',
        'telefono',
        'tipo_usuario',
        'password',
        'mfa',
    ];

    protected $hidden = ['password'];

    // Mutador para encriptar automáticamente la contraseña si se asigna
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }
}
