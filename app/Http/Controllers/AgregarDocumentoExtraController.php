<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AgregarDocumentoExtraController extends Controller
{
    public function register(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
        
        $validator = Validator::make($request->all(), [
            'estadia_id' => 'required|integer',
            'nombre' => 'required|string',
            'ruta' => 'required|string',
            'fecha_subida' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $docExtra = AgregarDocumentoExtra::create([
            'estadia_id' => $request->estadia_id,
            'nombre' => $request->nombre,
            'ruta' => $request->ruta,
            'fecha_subida' => $request->fecha_subida,
        ]);

        return response()->json(['message' => 'Documento agregado con éxito', 'docExtra' => $docExtra], 201);
    }

    public function update(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $docExtra = AgregarDocumentoExtra::find($request->input('id'));

        if(!$docExtra){
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        $docExtra->update($request->all());
        return response()->json(['message' => 'Documento actualizado con éxito', 'docExtra' => $docExtra], 200);
    }

    public function delete(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $docExtra = AgregarDocumentoExtra::find($request->input('id'));

        if(!$docExtra){
            return response()->json(['message' => 'Documento no encontrado'], 404);
        }

        $docExtra->delete();
        return response()->json(['message' => 'Documento eliminado con éxito'], 200);
    }

    public function verDocExtra(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');

        $docExtra = AgregarDocumentoExtra::find($id);

        if(!$docExtra){
            return response()->json(['message' => 'Documetno no encontrado'], 404);
        }

        return response()->json(['documento' => $docExtra], 200);
    }

    public function listaDocExtra(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        try {
            $docExtras = AgregarDocumentoExtra::all();
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la lista de documentos', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['docExtras' => $docExtras], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\User';
    }
}
