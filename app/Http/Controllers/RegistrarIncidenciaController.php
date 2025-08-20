<?php

namespace App\Http\Controllers;

use App\Models\RegistrarIncidencia;
use App\Models\Estadia;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class RegistrarIncidenciaController extends Controller
{

    public function register(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $validator = Validator::make($request->all(), [
            'estadia_id' => 'required|integer',
            'descripcion' => 'required|string',
            'fecha' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $incidencia = RegistrarIncidencia::create([
            'estadia_id' => $request->estadia_id,
            'descripcion' => $request->descripcion,
            'fecha' => $request->fecha,
        ]);

        return response()->json(['message' => 'Incidencia registrada con éxito', 'incidencia' => $incidencia], 201);
    }

    public function update(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $incidencia = RegistrarIncidencia::find($request->input('id'));

        if(!$incidencia){
            return response()->json(['message' => 'Incidencia no encontrada'], 404);
        }

        $incidencia->update($request->all());
        return response()->json(['message' => 'Incidencia actualizada con éxito', 'incidencia' => $incidencia], 200);
    }

    public function delete(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');

        $incidencia = RegistrarIncidencia::find($id);

        if(!$incidencia){
            return response()->json(['message' => 'Incidencia no encontrada'], 404);
        }

        $incidencia->delete();
        return response()->json(['message' => 'Incidencia eliminada con éxito'], 200);
    }

    public function verIncidencia(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');

        $incidencia = RegistrarIncidencia::find($id);

        if(!$incidencia){
            return response()->json(['message', 'Incidencia no encontrada'], 404);
        }

        return response()->json(['incidencia' => $incidencia], 200);
    }

    public function listaIncidencias(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        try {
            $incidencias = RegistrarIncidencia::all();
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la lista de incidencias', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['incidencias' => $incidencias], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\User';
    }
}
