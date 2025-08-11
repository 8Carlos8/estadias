<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartaAceptacion;
use App\Models\Estadia;
use Illuminate\Support\Facades\Storage; 

class CartaAceptacionController extends Controller
{
    /**
     *  Guarda la carta de aceptación recibida por parte de la empresa. Actualizar el seguimiento conforme a lo que esta en el campo de estadias seguimiento 
     */
   public function registrarCartaAceptacion(Request $request)
{
    $token = $request->input('token');
    if(!$this->validateToken($token)){
        return response()->json(['message' => 'Token inválido'], 401);
    }

    $request->validate([
        'estadia_id' => 'required|integer|exists:estadias,id',
        'fecha_recepcion' => 'required|date',
        'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'observaciones' => 'nullable|string',
    ]);

    // Guardar el archivo en storage/app/public/cartas_aceptacion
    $rutaArchivo = $request->file('documento')->store('cartas_aceptacion', 'public');

    // Crear el registro con la ruta del archivo guardado
    $carta = CartaAceptacion::create([
        'estadia_id' => $request->estadia_id,
        'fecha_recepcion' => $request->fecha_recepcion,
        'ruta_documento' => $rutaArchivo, // Se guarda solo la ruta relativa
        'observaciones' => $request->observaciones,
    ]);

    return response()->json([
        'mensaje' => 'Carta de aceptación registrada correctamente',
        'carta' => $carta
    ], 201);
}


    /**
    
     */
  

    /**
     *  Devuelve la carta de aceptación vinculada a una estadía (por Request)
     */
    public function obtenerCartaAceptacionPorEstadia(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $request->validate([
            'estadia_id' => 'required|exists:estadias,id'
        ]);

        $carta = CartaAceptacion::where('estadia_id', $request->estadia_id)->first();

        if (!$carta) {
            return response()->json([
                'mensaje' => 'No se encontró ninguna carta para la estadía indicada.'
            ], 404);
        }

        return response()->json([
            'carta_aceptacion' => $carta
        ], 200);
    }

    /**
     *  Actualiza una carta de aceptación, elimiar el archivo que ya existe y subir el nuevo
     */
    public function update(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $request->validate([
            'id' => 'required|exists:cartas_aceptacion,id',
            'fecha_recepcion' => 'nullable|date',
            'ruta_documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        $carta = CartaAceptacion::find($request->id);

        if (!$carta) {
            return response()->json(['mensaje' => 'Carta no encontrada'], 404);
        }

        $carta->update($request->only(['fecha_recepcion', 'ruta_documento', 'observaciones']));

        return response()->json([
            'mensaje' => 'Carta actualizada correctamente',
            'carta' => $carta
        ])->setStatusCode(200);
    }

    /**
     *  Elimina una carta de aceptación por ID desde request, eliminar el archivo tambien
     */
    public function destroy(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $request->validate([
            'id' => 'required|exists:cartas_aceptacion,id'
        ]);

        $carta = CartaAceptacion::find($request->id);

        if (!$carta) {
            return response()->json(['mensaje' => 'Carta no encontrada'], 404);
        }

        $carta->delete();

        return response()->json(['mensaje' => 'Carta eliminada correctamente'])
            ->setStatusCode(200);
    }

    /**
     *  Listar todas las cartas
     */
    public function listarTodas(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
        
        $cartas = CartaAceptacion::orderBy('id')->get(); // Orden por id

        return response()->json(['cartas_aceptacion' => $cartas], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\Usuario';
    }
}