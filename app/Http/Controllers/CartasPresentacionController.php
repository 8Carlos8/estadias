<?php

namespace App\Http\Controllers;

use App\Models\Cartas_presentacion;
use App\Models\Estadia_seguimiento;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;

class CartasPresentacionController extends Controller
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
            'estadia_id' => 'required|integer',
            'fecha_emision' => 'required|date',
            'ruta_documento'=> 'required|file',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Guardar archivo con el nombre original
        $file = $request->file('ruta_documento');
        $originalName = $file->getClientOriginalName();
        $path = $file->storeAs('cartas', $originalName, 'public'); // mantiene el nombre original

        $cartaPres = Cartas_presentacion::create([
            'estadia_id' => $request->estadia_id,
            'tutor_id' => $usuario->id,
            'fecha_emision' => $request->fecha_emision,
            'ruta_documento' => $path,
            'firmada_director' => false,
        ]);

        // Verificar si ya existe un seguimiento para esa estadía
        $seguimiento = Estadia_seguimiento::where('estadia_id', $request->estadia_id)->first();
        
        if ($seguimiento) {
            $seguimiento->etapa = 'presentacion';
            $seguimiento->estatus = 'pendiente';
            $seguimiento->comentario = 'Carta de presentación creada';
            $seguimiento->fecha_actualizacion = now();
            $seguimiento->actualizado_por = $usuario->id; //Cmbiar esto por el campo del id
            $seguimiento->save();
        } else {
            // Si no existe seguimiento, crear uno nuevo
            Estadia_seguimiento::create([
                'estadia_id' => $request->estadia_id,
                'etapa' => 'presentacion',
                'estatus' => 'pendiente',
                'comentario' => 'Seguimiento generado automáticamente al registrar carta',
                'fecha_actualizacion' => now(),
                'actualizado_por' => $usuario->id, //Cmbiar esto por el campo del id
            ]);
        }

        $url = asset('storage/' . $path);

        return response()->json(['message' => 'Carta registrada con éxito y seguimiento actualizado', 'cartaPres' => $cartaPres, 'seguimiento' => $seguimiento, 'url_documento' => $url], 201);
    }

    public function update(Request $request)
    {
    
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $usuario = $accessToken->tokenable;

        $cartaPres = Cartas_presentacion::find($request->input('id'));

        if(!$cartaPres){
            return response()->json(['message' => 'Carta de presentación no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'estadia_id' => 'sometimes|integer',
            'fecha_emision' => 'sometimes|date',
            'ruta_documento' => 'sometimes|file', // archivo opcional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Si hay un archivo nuevo
        if ($request->hasFile('ruta_documento')) {
            // Eliminar archivo anterior
            if ($cartaPres->ruta_documento && Storage::disk('public')->exists($cartaPres->ruta_documento)) {
                Storage::disk('public')->delete($cartaPres->ruta_documento);
            }

            // Guardar archivo con el nombre original
            $file = $request->file('ruta_documento');
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs('cartas', $originalName, 'public');

            $cartaPres->ruta_documento = $path;
        }

        // Actualizar otros campos
        if ($request->has('estadia_id')) $cartaPres->estadia_id = $request->estadia_id;
        if ($request->has('tutor_id')) $cartaPres->tutor_id = $usuario->id;
        if ($request->has('fecha_emision')) $cartaPres->fecha_emision = $request->fecha_emision;

        $cartaPres->save();

        $url = asset('storage/' . $cartaPres->ruta_documento);

        $cartaPres->update($request->all());
        return response()->json(['message' => 'Carta de presentación actualizada con éxito', 'cartaPres' => $cartaPres, 'url_documento' => $url], 200);
    }

    public function delete(Request $request)
    {

        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $usuario = $accessToken->tokenable;

        $cartaPres = Cartas_presentacion::find($request->input('id'));

        if(!$cartaPres){
            return response()->json(['message' => 'Carta de presentación no encontrada'], 404);
        }

        // eliminar archivo
        if ($cartaPres->ruta_documento && Storage::disk('public')->exists($cartaPres->ruta_documento)) {
            Storage::disk('public')->delete($cartaPres->ruta_documento);
        }

        $estadiaId = $cartaPres->estadia_id;
        $cartaPres->delete();

        // volver a la etapa anterior
        $seguimiento = Estadia_seguimiento::where('estadia_id', $estadiaId)->first();
        if ($seguimiento) {
            $seguimiento->etapa = 'solicitud';
            $seguimiento->estatus = 'pendiente';
            $seguimiento->comentario = 'Carta de presentación eliminada, regreso a seguimiento inicial';
            $seguimiento->fecha_actualizacion = now();
            $seguimiento->actualizado_por = $usuario->id;
            $seguimiento->save();
        } else {
            Estadia_seguimiento::create([
                'estadia_id' => $estadiaId,
                'etapa' => 'solicitud',
                'estatus' => 'pendiente',
                'comentario' => 'Seguimiento inicial generado automáticamente',
                'fecha_actualizacion' => now(),
                'actualizado_por' => $usuario->id,
            ]);
        }

        return response()->json(['message' => 'Carta de presentación eliminada con éxito'], 200);
    }

    public function verCartaPres(Request $request)
    {
    
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');

        $cartaPres = Cartas_presentacion::find($id);
        if(!$cartaPres){
            return response()->json(['message' => 'Carta de presentación no encontrada'], 404);
        }

        return response()->json(['cartaPres' => $cartaPres], 200);
    }

    public function listaCartasPres(Request $request)
    {
        
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        try {
            $cartasPres = Cartas_presentacion::all();
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la lista de cartas', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['cartasPres' => $cartasPres], 200);
    }

    public function firmaCartaPres(Request $request)
    {
        
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');
        $cartaPres = Cartas_presentacion::find($id);

        if(!$cartaPres){
            return response()->json(['message' => 'Carta no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'ruta_documento' => 'sometimes|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Si hay un archivo nuevo, lo almacenamos, comprobar esto ya que creo que no funciona XDXD
        if ($request->hasFile('ruta_documento')) {
            $path = $request->file('ruta_documento')->store('cartas', 'public');
            $cartaPres->ruta_documento = $path;
        }

        $url = asset('storage/' . $cartaPres->ruta_documento);

        //Cambiar el estado en la parte del seguimiento del tramite
        $cartaPres->firmada_director = true;
        $cartaPres->save();

        // Actualizar seguimiento de la estadía
        $seguimiento = Estadia_seguimiento::where('estadia_id', $cartaPres->estadia_id)->first();
        if($seguimiento){
            $seguimiento->etapa = 'firma_director';
            $seguimiento->estatus = 'completado';
            $seguimiento->comentario = 'Carta de presentación creada y firmada por el director';
            $seguimiento->fecha_actualizacion = now();
            $seguimiento->save();
        }

        return response()->json(['message' => 'Carta firmada por el director y seguimiento actualizado', 'cartaPres' => $cartaPres, 'seguimiento' => $seguimiento, 'url_documento' => $url], 200);
    }

    public function descargarCartaPres(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');
        $cartaPres = Cartas_presentacion::find($id);

        if(!$cartaPres){
            return response()->json(['message' => 'Carta no encontrada'], 404);
        }

        //Checar la ruta del pdf 
        $ruta = $cartaPres->ruta_documento;
        $url = asset('storage/' . $ruta);

        return response()->json(['url_descarga' => $url], 200);
    }

    public function contarCartasPres(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        // Contar cartas
        $count = Cartas_presentacion::count();

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

        return response()->json(['total_cartasPres' => $count, 'total_alumnos' => $nombresAlumnos->count(), 'nombres_alumnos' => $nombresAlumnos], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\Usuario';
    }
}
