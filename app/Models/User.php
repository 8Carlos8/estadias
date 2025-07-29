<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
   

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

    protected $casts = [
        'mfa' => 'boolean',
    ];

    /**
     * Mutador para encriptar automáticamente la contraseña
     */
    public function setPasswordAttribute($value)
    {
        
        if ($value && !preg_match('/^\$2y\$/', $value)) {
            $this->attributes['password'] = bcrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }
}
