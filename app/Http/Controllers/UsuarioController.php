<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    //Agregar el actualizar, eliminar, buscar por id y listar todos los usuarios
    // registrarUsuario($datos)
    public function registrarUsuario(Request $request)
    {

        $request->validate([
            'nombre' => 'required|string',
            'apellido_paterno' => 'required|string',
            //Agregar el campo de apellido_materno
            'curp' => 'required|string|size:18|unique:usuarios,curp',
            'correo' => 'required|email|unique:usuarios,correo',
            'telefono' => 'nullable|string|max:20', //10 digitos
            'tipo_usuario' => 'required|in:estudiante,docente,admin',//Checar si es con numero o con el nombre del rol
            'password' => 'required|string|min:6',//12 caracteres 
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
        //Comprobar si esta activada
        if ($usuario->mfa) {
            return response()->json([
                'mensaje' => 'Autenticación multifactor requerida',
                'mfa' => true //Logica del envio del codigo de verificación
            ]);
        }

        //Crear el token de la sesión y ya empezar a devolver las propiedades importantes, ID, Nombre, tipo_usuario
        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso',
            'usuario' => $usuario
        ]);
    }

    // obtenerUsuarioPorCorreo($correo), cambiar el parametro pa que reciba los request
    public function obtenerUsuarioPorCorreo($correo)
    {
        //Agregar la parte de la verificación del token pa acceder a las funciones
        //Agregar la parte del input para que ahi se haga la consulta
        $usuario = Usuario::where('correo', $correo)->first();

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        //Agregar el nombre al objeto
        return response()->json($usuario);
    }

    // actualizarPassword($usuario_id, $nuevaPassword), quitar el parametro de usuario_id
    public function actualizarPassword(Request $request, $usuario_id)
    {
        //Agregar la parte de la verificación del token pa acceder a las funciones
        //Agergar función de solicitar contraseña pa que se envie el código y despues ya acceder a la función de actualizar y aplicar la logica del cambio de contraseña
        $request->validate([
            'nueva_password' => 'required|string|min:6',
        ]);

        //Agregar la parte del input para que ahi se haga la consulta
        $usuario = Usuario::find($usuario_id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario->password = $request->nueva_password; // Hashea por el mutador
        $usuario->save();

        return response()->json(['mensaje' => 'Contraseña actualizada correctamente']);
    }

    // activarMFA($usuario_id), cambiar el parametro pa que reciba los request
    public function activarMFA($usuario_id)
    {
        //Agregar la parte de la verificación del token pa acceder a las funciones
        //Agregar la parte del input para que ahi se haga la consulta
        $usuario = Usuario::find($usuario_id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario->mfa = 'activo'; // puedes usar un código, booleano, o 'activo', True
        $usuario->save();

        return response()->json(['mensaje' => 'MFA activado para el usuario']);
    }
}
