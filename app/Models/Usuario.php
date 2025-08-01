<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

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
