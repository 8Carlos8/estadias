<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

class UsuarioController extends Controller
{
    // registrarUsuario
    public function registrarUsuario(Request $request)
    {

        $request->validate([
            'nombre' => 'required|string',
            'apellido_paterno' => 'required|string',
            'apellido_materno' => 'required|string',
            'curp' => 'required|string|size:18|unique:users,curp',
            'correo' => 'required|email|unique:users,correo',
            'telefono' => 'nullable|string|max:10',
            'tipo_usuario' => 'required|in:estudiante,docente,admin',
            'password' => 'required|string|min:12',
        ]);
        
        $datos = $request->all();
        $datos['password'] = bcrypt($datos['password']);
        
        $usuario = Usuario::create($datos);
        
        return response()->json([
            'mensaje' => 'Usuario registrado correctamente',
            'usuario' => $usuario
        ], 201);
    }
    
    // iniciarSesion
    public function iniciarSesion(Request $request)
    {
        // No valido token aquí porque aqui lo obtengo
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
        
        // Aquí teiene que generar un token real :)
        //$token = 'token_generado_para_usuario';
        $token = $usuario->createToken('Token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso',
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'tipo_usuario' => $usuario->tipo_usuario,
                'token' => $token,
                ]
            ], 200);
    }

    public function logout(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if ($accessToken) {
            $accessToken->delete();
        }

        return response()->json(['message' => 'Sesión cerrada con éxito']);
    }
        
    // obtenerUsuarioPorCorreo
    public function obtenerUsuarioPorCorreo(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
        
        $request->validate([
            'correo' => 'required|email'
        ]);
        
        $usuario = Usuario::where('correo', $request->correo)->first();
        
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }
        
        return response()->json($usuario);
    }
    
    // actualizarPassword
    public function actualizarPassword(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
        
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'nueva_password' => 'required|string|min:12',
        ]);

        $usuario = Usuario::find($request->usuario_id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario->password = bcrypt($request->nueva_password);
        $usuario->save();
        
        return response()->json(['mensaje' => 'Contraseña actualizada correctamente']);
    }

    // activarMFA
    public function activarMFA(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
        
        $request->validate([
            'usuario_id' => 'required|exists:users,id'
        ]);

        $usuario = Usuario::find($request->usuario_id);
        
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }
        
        $usuario->mfa = true;
        $usuario->save();
        
        return response()->json(['mensaje' => 'MFA activado para el usuario']);
    }
    
    // actualizarUsuario
    public function actualizarUsuario(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
        
        $request->validate([
            'id' => 'required|exists:users,id',
            'nombre' => 'required|string',
            'apellido_paterno' => 'required|string',
            'apellido_materno' => 'required|string',
            'curp' => 'required|string|size:18|unique:users,curp,' . $request->id,
            'correo' => 'required|email|unique:users,correo,' . $request->id,
            'telefono' => 'nullable|string|max:10',
            'tipo_usuario' => 'required|in:estudiante,docente,admin',
            'password' => 'required|string|min:12',
        ]);
        
        $usuario = Usuario::find($request->id);
        
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario->update([
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'curp' => $request->curp,
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'tipo_usuario' => $request->tipo_usuario,
            'password' => bcrypt($request->password),
        ]);
        
        return response()->json([
            'mensaje' => 'Usuario actualizado correctamente',
            'usuario' => $usuario
            ])->setStatusCode(200);
    }
        
    // eliminarUsuario
    public function eliminarUsuario(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
        
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);
        
        $usuario = Usuario::find($request->id);
        
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }
        
        $usuario->delete();
        
        return response()->json(['mensaje' => 'Usuario eliminado correctamente'])
        ->setStatusCode(200);
    }
    
    // obtenerUsuarioPorId
    public function obtenerUsuarioPorId(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
        
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);
        
        $usuario = Usuario::find($request->id);
        
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }
        
        return response()->json(['usuario' => $usuario])
        ->setStatusCode(200);
    }
    
    // listarUsuarios, checar esta, solo pedir el token y devolver la lista de los usuarios
    public function listarUsuarios(Request $request)
    {
        
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $request->validate([
            'id' => 'nullable|exists:users,id'
        ]);
        
        if ($request->has('id')) {
            $usuario = Usuario::find($request->id);
            
            if (!$usuario) {
                return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
            }
            
            return response()->json(['usuarios' => [$usuario]])
            ->setStatusCode(200);
        }
        
        $usuarios = Usuario::orderBy('nombre')->get();
        
        return response()->json(['usuarios' => $usuarios])
        ->setStatusCode(200);
    }

    // Función privada para validar el token
    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\Usuario';
    }

}
