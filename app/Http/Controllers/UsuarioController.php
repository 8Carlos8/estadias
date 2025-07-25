<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // registrarUsuario($datos)
    public function registrarUsuario(Request $request)
    {

        $request->validate([
            'nombre' => 'required|string',
            'apellido_paterno' => 'required|string',
            'curp' => 'required|string|size:18|unique:usuarios,curp',
            'correo' => 'required|email|unique:usuarios,correo',
            'telefono' => 'nullable|string|max:20',
            'tipo_usuario' => 'required|in:estudiante,docente,admin',
            'password' => 'required|string|min:6',
        ]);

        $usuario = Usuario::create($request->all());

        return response()->json([
            'mensaje' => 'Usuario registrado correctamente',
            'usuario' => $usuario
        ], 201);
    }

    // iniciarSesion($correo, $password)
    public function iniciarSesion(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where('correo', $request->correo)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['mensaje' => 'Credenciales inválidas'], 401);
        }

        // Si el usuario tiene MFA activado
        if ($usuario->mfa) {
            return response()->json([
                'mensaje' => 'Autenticación multifactor requerida',
                'mfa' => true
            ]);
        }

        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso',
            'usuario' => $usuario
        ]);
    }

    // obtenerUsuarioPorCorreo($correo)
    public function obtenerUsuarioPorCorreo($correo)
    {
        $usuario = Usuario::where('correo', $correo)->first();

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        return response()->json($usuario);
    }

    // actualizarPassword($usuario_id, $nuevaPassword)
    public function actualizarPassword(Request $request, $usuario_id)
    {
        $request->validate([
            'nueva_password' => 'required|string|min:6',
        ]);

        $usuario = Usuario::find($usuario_id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario->password = $request->nueva_password; // Hashea por el mutador
        $usuario->save();

        return response()->json(['mensaje' => 'Contraseña actualizada correctamente']);
    }

    // activarMFA($usuario_id)
    public function activarMFA($usuario_id)
    {
        $usuario = Usuario::find($usuario_id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario->mfa = 'activo'; // puedes usar un código, booleano, o 'activo'
        $usuario->save();

        return response()->json(['mensaje' => 'MFA activado para el usuario']);
    }
}
