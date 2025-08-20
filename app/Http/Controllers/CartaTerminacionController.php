<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartaTerminacion;
use App\Models\Estadia_seguimiento;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;

class CartaTerminacionController extends Controller
{
    public function register(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $usuario = $accessToken->tokenable;

        $validator = Validator::make($request->all(), [
            'estadia_id' => 'required|integer',
            'fecha_subida' => 'required|date',
            'documento' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('documento');
        $originalName = $file->getClientOriginalName();
        $path = $file->storeAs('cartas_terminacion', $originalName, 'public');

        $cartaTer = CartaTerminacion::create([
            'estadia_id' => $request->estadia_id,
            'tutor_id' => $usuario->id,
            'fecha_subida' => $request->fecha_subida,
            'documento' => $path,
        ]);

        $seguimiento = Estadia_seguimiento::where('estadia_id', $request->estadia_id)->first();

        if ($seguimiento) {
            $seguimiento->etapa = 'finalizacion';
            $seguimiento->estatus = 'completado';
            $seguimiento->comentario = 'Carta de terminación subida con éxito';
            $seguimiento->fecha_actualizacion = now();
            $seguimiento->actualizado_por = $usuario->id;
            $seguimiento->save();
        } else {
            Estadia_seguimiento::create([
                'estadia_id' => $request->estadia_id,
                'etapa' => 'finalizacion',
                'estatus' => 'completado',
                'comentario' => 'Carta de terminación subida con éxito',
                'fecha_actualizacion' => now(),
                'actualizado_por' => $usuario->id,
            ]);
        }

        $url = asset('storage' . $path);

        return response()->json(['message' => 'Carta registrada con éxito y seguimiento actualizado', 'cartaTer' => $cartaTer, 'seguimiento' => $seguimiento, 'url_documento' => $url], 201);
    }

    public function update(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $usuario = $accessToken->tokenable;

        $cartaTer = CartaTerminacion::find($request->input('id'));

        if (!$cartaTer) {
            return response()->json(['message' => 'Carta de terminación no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'estadia_id' => 'integer',
            'fecha_subida' => 'date',
            'documento' => 'file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('documento')) {
            if ($cartaTer->documento && Storage::disk('public')->exists($cartaTer->documento)) {
                Storage::disk('public')->delete($cartaTer->documento);
            }

            $file = $request->file('documento');
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs('cartas_terminacion', $originalName, 'public');

            $cartaTer->documento = $path;
        }

        if ($request->has('estadia_id')) $cartaTer->estadia_id = $request->estadia_id;
        if ($request->has('tutor_id')) $cartaTer->tutor_id = $request->tutor_id;
        if ($request->has('fecha_subida')) $cartaTer->fecha_subida = $request->fecha_subida;

        $cartaTer->save();

        $url = asset('storage/' . $cartaTer->documento);

        $cartaTer->update($request->all());

        return response()->json(['message' => 'Carta de terminación actualizada con éxito', 'cartaTer' => $cartaTer, 'documento' => $url], 200);
    }

    public function delete(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $usuario = $accessToken->tokenable;

        $cartaTer = CartaTerminacion::find($request->input('id'));

        if(!$cartaTer){
            return response()->json(['message' => 'Carta de terminación no encontrada'], 404);
        }

        // eliminar archivo
        if ($cartaTer->documento && Storage::disk('public')->exists($cartaTer->documento)) {
            Storage::disk('public')->delete($cartaTer->documento);
        }

        $estadiaId = $cartaTer->estadia_id;
        $cartaTer->delete();

        // volver a la etapa anterior
        $seguimiento = Estadia_seguimiento::where('estadia_id', $estadiaId)->first();
        if ($seguimiento) {
            $seguimiento->etapa = 'registro_final';
            $seguimiento->estatus = 'pendiente';
            $seguimiento->comentario = 'Carta de terminación eliminada, regreso a seguimiento de registro final';
            $seguimiento->fecha_actualizacion = now();
            $seguimiento->actualizado_por = $usuario->id;
            $seguimiento->save();
        } else {
            Estadia_seguimiento::create([
                'estadia_id' => $estadiaId,
                'etapa' => 'registro_final',
                'estatus' => 'pendiente',
                'comentario' => 'Carta de terminación eliminada, regreso a seguimiento de registro final',
                'fecha_actualizacion' => now(),
                'actualizado_por' => $usuario->id,
            ]);
        }

        return response()->json(['message' => 'Carta de terminación eliminada con éxito'], 200);
    }

    public function verCartaTer(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');

        $cartaTer = CartaTerminacion::find($id);
        if(!$cartaTer){
            return response()->json(['message' => 'Carta de terminación no encontrada'], 404);
        }

        return response()->json(['cartaTer' => $cartaTer], 200);
    }

    public function listaCartasTer(Request $request)
    {
        
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        try {
            $cartasTer = CartaTerminacion::all();
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la lista de cartas', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['cartasTer' => $cartasTer], 200);
    }

    public function descargarCartaTer(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');
        $cartaTer = CartaTerminacion::find($id);

        if(!$cartaTer){
            return response()->json(['message' => 'Carta no encontrada'], 404);
        }

        //Checar la ruta del pdf 
        $ruta = $cartaTer->documento;
        $url = asset('storage/' . $ruta);

        return response()->json(['url_descarga' => $url], 200);
    }

    public function contarCartasTer(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        // Contar cartas
        $count = CartaTerminacion::count();

        //Devolver los alumnos
        $alunmnos = Usuario::whereIn('id', function ($query){
            $query->select('usuario_id')
                ->from('estadia')
                ->whereIm('id', function ($sub){
                    $sub->select('estadias_id')
                        ->from('cartas_presentacion');
                });
        })->get();

        //Construir arreglo con los nombres completos
        $nombresAlumnos = $alunmnos->map(function ($alumno){
            return $alumno->nombre . ' ' . $alumno->apellido_paterno . ' ' . $alumno->apellido_materno;
        });

        return response()->json(['total_cartasTer' => $count], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\Usuario';
    }
}
