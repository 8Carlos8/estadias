<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
   
    //  registrarUsuario($datos)
    public function registrarUsuario(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'apellido_paterno' => 'required|string',
            'apellido_materno' => 'required|string',
            'curp' => 'required|string|size:18|unique:usuarios,curp',
            'correo' => 'required|email|unique:usuarios,correo',
            'telefono' => 'nullable|string|max:20',
            'tipo_usuario' => 'required|in:estudiante,docente,admin',
            'password' => 'required|string|min:6',
        ]);

        $datos = $request->all();
        $datos['password'] = bcrypt($datos['password']);

        $usuario = Usuario::create($datos);

        return response()->json([
            'mensaje' => 'Usuario registrado correctamente',
            'usuario' => $usuario
        ], 201);
    }

    //  iniciarSesion($correo, $password)
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

        if ($usuario->mfa) {
            return response()->json([
                'mensaje' => 'Autenticación multifactor requerida',
                'mfa' => true
            ]);
        }

        

        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso',
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'tipo_usuario' => $usuario->tipo_usuario,
                
            ]
        ]);
    }

    //  obtenerUsuarioPorCorreo 
    public function obtenerUsuarioPorCorreo(Request $request)
    {
        $request->validate([
            'correo' => 'required|email'
        ]);

        $usuario = Usuario::where('correo', $request->correo)->first();

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        return response()->json($usuario);
    }

    //  actualizarPassword 
    public function actualizarPassword(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'nueva_password' => 'required|string|min:6',
        ]);

        $usuario = Usuario::find($request->usuario_id);
        $usuario->password = bcrypt($request->nueva_password);
        $usuario->save();

        return response()->json(['mensaje' => 'Contraseña actualizada correctamente']);
    }

    //  activarMFA 
    public function activarMFA(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id'
        ]);

        $usuario = Usuario::find($request->usuario_id);
        $usuario->mfa = true;
        $usuario->save();

        return response()->json(['mensaje' => 'MFA activado para el usuario']);
    }

    //  actualizar usuario
    public function actualizarUsuario(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:usuarios,id',
            'nombre' => 'nullable|string',
            'apellido_paterno' => 'nullable|string',
            'apellido_materno' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'tipo_usuario' => 'nullable|in:estudiante,docente,admin',
        ]);

        $usuario = Usuario::find($request->id);
        $usuario->update($request->only([
            'nombre', 'apellido_paterno', 'apellido_materno',
            'telefono', 'tipo_usuario'
        ]));

        return response()->json(['mensaje' => 'Usuario actualizado correctamente', 'usuario' => $usuario]);
    }

    //  eliminar usuario
    public function eliminarUsuario(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:usuarios,id'
        ]);

        $usuario = Usuario::find($request->id);
        $usuario->delete();

        return response()->json(['mensaje' => 'Usuario eliminado correctamente']);
    }

    //  obtener usuario por ID
    public function obtenerUsuarioPorId(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:usuarios,id'
        ]);

        $usuario = Usuario::find($request->id);
        return response()->json($usuario);
    }

    //  listar todos los usuarios
    public function listarUsuarios()
    {
        $usuarios = Usuario::orderBy('nombre')->get();
        return response()->json($usuarios);
    }
}
