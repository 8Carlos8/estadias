<?php

namespace App\Http\Controllers;

use App\Models\ProgramarVisita;
use App\Models\Estadia;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class ProgramarVisitaController extends Controller
{
    public function register(Request $request)
    {
        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
            */

        $validator = Validator::make($request->all(), [
            'estadia_id' => 'required|integer',
            'user_id' => 'required|integer',
            'fecha' => 'required',
            'hora' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $visita = ProgramarVisita::create([
            'estadia_id' => $request->estadia_id,
            'user_id' => $request->user_id,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
        ]);

        return response()->json(['message' => 'Visita agregada con éxito', 'visita' => $visita], 201);
    }

    public function update(Request $request)
    {
        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
            */

        $visita = ProgramarVisita::find($request->input('id'));

        if(!$visita){
            return response()->json(['message' => 'Visita no encontrada'], 404);
        }

        $visita->update($request->all());
        return response()->json(['message' => 'Visita actualiza con éxito', 'visita' => $visita], 200);
    }

    public function delete(Request $request)
    {
        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        } */

        $visita = ProgramarVisita::find($request->input('id'));

        if(!$visita){
            return response()->json(['message' => 'Visita no encontrada'], 404);
        }

        $visita->delete();
        return response()->json(['message' => 'Visita eliminada con éxito'], 200);
    }

    public function verVisita(Request $request)
    {
        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
            */

        $id = $request->input('id');

        $visita = ProgramarVisita::find($id);

        if(!$visita){
            return response()->json(['message' => 'Visita no encontrada'], 404);
        }

        return response()->json(['visita' => $visita], 200);
    }

    public function listaVisitas(Request $request)
    {
        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
            */

        try {
            $visitas = ProgramarVisita::all();
        } catch (Exceprion $e) {
            return reponse()->json(['message' => 'Error al obetner la lista de visitas', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['visitas' => $visitas], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\User';
    }
}
