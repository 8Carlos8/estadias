<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Estadia;
use App\Models\Estadia_seguimiento;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class EstadiaController extends Controller
{

    public function register(Request $request)
    {

        //Con esta validación del token se acceden a sus propiedades
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        //Aca es donde se pueden acceder a las propiedades
        $usuario = $accessToken->tokenable;

        $validator = Validator::make($request->all(), [
            'alumno_id' => 'required|integer',
            'empresa' => 'required|string',
            'asesor_externo' => 'required|string',
            'proyecto_nombre' => 'required|string',
            'duracion_semanas' => 'required|integer',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'apoyo' => 'required|string',
        ]);

        //Si la validación falla, devolver un error
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $estadia = Estadia::create([
            'alumno_id' => $request->alumno_id,
            'empresa' => $request->empresa,
            'asesor_externo' => $request->asesor_externo,
            'proyecto_nombre' => $request->proyecto_nombre,
            'duracion_semanas' => $request->duracion_semanas,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'apoyo' => $request->apoyo,
            'estatus' => 'solicitada',
        ]);

        // 2. Crear seguimiento inicial
        Estadia_seguimiento::create([
            'estadia_id' => $estadia->id,
            'etapa' => 'solicitud',
            'estatus' => 'pendiente',
            'comentario' => 'Seguimiento inicial generado automáticamente',
            'fecha_actualizacion' => now(),
            'actualizado_por' => $usuario->id, //Cmbiar esto por el campo del id
        ]);

        return response()->json(['message' => 'Estadia registrada con éxito y seguimiento inicial creado', 'estadia' => $estadia], 201);
    }

    public function update(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $estadia = Estadia::find($request->input('id'));

        if(!$estadia){
            return response()->json(['message' => 'Estadia no encontrada'], 404);
        }

        $estadia->update($request->all());
        return response()->json(['message' => 'Estadia actualizada con éxito' , 'estadia' => $estadia], 200);
    }

    public function delete(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $estadia = Estadia::find($request->input('id'));

        if(!$estadia){
            return response()->json(['message' => 'Estadia no encontrada'], 404);
        }

        $estadia->delete();
        return response()->json(['message' => 'Estadia eliminada con éxito'], 200);
    }

    public function verEstadia(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');

        $estadia = Estadia::find($id);
        if(!$estadia){
            return response()->json(['message' => 'Estadia no encontrada'], 404);
        }

        return response()->json(['estadia' => $estadia], 200);
    }

    public function listaEstadias(Request $request)
    {
        
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        try {
            $estadias = Estadia::all();
        } catch (Exception $e) {
            return reponse()->json(['message' => 'Error al obtener la lista de estadias', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['estdias' => $estadias], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\Usuario';
    }
}
