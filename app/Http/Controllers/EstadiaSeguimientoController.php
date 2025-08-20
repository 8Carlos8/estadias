<?php

namespace App\Http\Controllers;

use App\Models\Estadia_seguimiento;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;

class EstadiaSeguimientoController extends Controller
{
    public function register(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $validator = Validator::make($request->all(), [
            'estadia_id' => 'required|integer',
            'etapa' => 'required|integer',
            'estatus' => 'required|integer',
            'comentario'=> 'required|string',
            'fecha_actualizacion' => 'required|date',
            'actualizado_por' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $seguimiento = Estadia_seguimiento::create([
            'estadia_id' => $request->estadia_id,
            'etapa' => $request->etapa,
            'estatus' => $request->estatus,
            'comentario' => $request->comentario,
            'fecha_actualizacion' => $request->fecha_actualizacion,
            'actualizado_por' => $request->actualizado_por,
        ]);

        return response()->json(['message' => 'Seguimiento iniciado con éxito', 'seguimiento' => $seguimiento], 201);
    }

    public function update(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $seguimiento = Estadia_seguimiento::find($request->input('id'));

        if(!$seguimiento){
            return response()->json(['message' => 'Seguimiento no encontrado'], 404);
        }

        $seguimiento->update($request->all());
        return response()->json(['message' => 'Seguimiento actualizado con éxito', 'seguimiento' => $seguimiento], 200);
    }

    public function delete(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $seguimiento = Estadia_seguimiento::find($request->input('id'));

        if(!$seguimiento){
            return response()->json(['message' => 'Seguimiento no encontrado'], 404);
        }

        $seguimiento->delete();
        return response()->json(['message' => 'Seguimiento eliminado con éxito'], 200);
    }

    public function verSeguimiento(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input($id);

        $seguimiento = Estadia_seguimiento::find($id);
        if(!$seguimiento){
            return response()->json(['message' => 'Seguimiento no encontrado'], 404);
        }

        return response()->json(['seguimiento' => $seguimiento], 200);
    }

    public function listaSeguimientos(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        try {
            $seguimientos = Estadia_seguimiento::all();
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la lista de seguimientos', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['seguimientos' => $seguimientos], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\Usuario';
    }
}
