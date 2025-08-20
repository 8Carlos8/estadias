<?php

namespace App\Http\Controllers;

use App\Models\AgregarDocumentoExtra;
use App\Models\Estadia;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
            'ruta' => 'required|file',
            'fecha_subida' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Guardar archivo
        $path = $request->file('ruta')->store('documentos_extra', 'public');

        $docExtra = AgregarDocumentoExtra::create([
            'estadia_id' => $request->estadia_id,
            'nombre' => $request->nombre,
            'ruta' => $path,
            'fecha_subida' => $request->fecha_subida,
        ]);

        $url = asset('storage/' . $path);

        return response()->json(['message' => 'Documento agregado con éxito', 'docExtra' => $docExtra, 'url_archivo' => $url], 201);
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

        $validator = Validator::make($request->all(), [
            'nombre'       => 'sometimes|string',
            'ruta'         => 'sometimes|file',  // opcional
            'fecha_subida' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Si hay archivo nuevo
        if ($request->hasFile('ruta')) {
            // eliminar archivo anterior si existe
            if ($docExtra->ruta && Storage::disk('public')->exists($docExtra->ruta)) {
                Storage::disk('public')->delete($docExtra->ruta);
            }
            // subir nuevo archivo
            $path = $request->file('ruta')->store('documentos_extra', 'public');
            $docExtra->ruta = $path;
        }

        if ($request->has('nombre')) {
            $docExtra->nombre = $request->nombre;
        }
        if ($request->has('fecha_subida')) {
            $docExtra->fecha_subida = $request->fecha_subida;
        }

        $docExtra->save();

        $url = asset('storage/' . $docExtra->ruta);

        return response()->json(['message' => 'Documento actualizado con éxito', 'docExtra' => $docExtra, 'url_archivo' => $url], 200);
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

        // eliminar archivo 
        if ($docExtra->ruta && Storage::disk('public')->exists($docExtra->ruta)) {
            Storage::disk('public')->delete($docExtra->ruta);
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
