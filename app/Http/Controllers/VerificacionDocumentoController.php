<?php

namespace App\Http\Controllers;

use App\Models\Verificacion_documento;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;

class VerificacionDocumentoController extends Controller
{
    public function register(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|integer',
            'tipo_validacion' => 'required|integer',
            //'resultado' => '',
            'fecha_validacion'=> 'required|date',
            'observaciones' => 'string',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $verificacion = Verificacion_documento::create([
            'usuario_id' => $request->usuario_id,
            'tipo_validacion' => $request->tipo_validacion,
            'resultado' => true,
            'fecha_validacion' => $request->fecha_validacion,
            'observaciones' => $request->observaciones,
        ]);

        return response()->json(['message' => 'Verificacion iniciada con éxito', 'verificacion' => $verificacion], 201);
    }

    public function update(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $verificacion = Verificacion_documento::find($request->input('id'));

        if(!$verificacion){
            return response()->json(['message' => 'Verificacion no encontrada'], 404);
        }

        $verificacion->update($request->all());
        return response()->json(['message' => 'Verificacion actualizada con éxito', 'verificacion' => $verificacion], 200);
    }

    public function delete(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $verificacion = Verificacion_documento::find($request->input('id'));

        if(!$verificacion){
            return response()->json(['message' => 'Verificacion no encontrada'], 404);
        }

        $verificacion->delete();
        return response()->json(['message' => 'Verificacion eliminada con éxito'], 200);
    }

    public function verVerificacion(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');

        $verificacion = Verificacion_documento::find($id);
        if(!$verificacion){
            return response()->json(['message' => 'Verificacion no encontrada'], 404);
        }

        return response()->json(['verificacion' => $verificacion], 200);
    }

    public function listaVerificaciones(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        try {
            $verificaciones = Verificacion_documento::all();
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la lista de verificaciones', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['verificaciones' => $verificaciones], 200);
    }

    public function VerificacionesUsuario(Request $request)
    {
        $token = $request->input('token');
        if (!$this->validateToken($token)) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $usuario_id = $request->input('usuario_id');

        $verificaciones = Verificacion_documento::where('usuario_id', $usuario_id)->get();

        if ($verificaciones->isEmpty()) {
            return response()->json(['message' => 'No se encontraron verificaciones para este usuario'], 404);
        }

        return response()->json(['verificaciones' => $verificaciones], 200);
    }

    //función de contar documentos pendientes
    public function contarDocPendientes(Request $request)
    {
        $token = $request->input('token');
        if (!$this->validateToken($token)) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $pendientes = Verificacion_documento::whereNull('resultado')
                        ->orWhere('resultado', false)
                        ->count();

        return response()->json(['total_pendientes' => $pendientes], 200);
    }

    //función de contar documentos verificados
    public function contarDocVerificados(Request $request)
    {
        $token = $request->input('token');
        if (!$this->validateToken($token)) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $verificados = Verificacion_documento::where('resultado', true)->count();

        return response()->json(['total_verificados' => $verificados], 200);
    }

    //función de contar documentos urgentes
    public function contarDocUrgentes(Request $request)
    {
        $token = $request->input('token');
        if (!$this->validateToken($token)) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $urgentes = Verificacion_documento::where('urgente', true)->count();

        return response()->json(['total_urgente' => $urgentes], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\Usuario';
    }
}
