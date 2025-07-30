<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartaAceptacion;
use App\Models\Estadia;
use Illuminate\Support\Facades\Storage; 

class CartaAceptacionController extends Controller
{
    /**
     *  Guarda la carta de aceptación recibida por parte de la empresa.
     */
    public function registrarCartaAceptacion(Request $request)
    {
        $request->validate([
            'estadia_id' => 'required|integer|exists:estadias,id',
            'fecha_recepcion' => 'required|date',
            'ruta_documento' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        $carta = CartaAceptacion::create($request->all());

        return response()->json([
            'mensaje' => 'Carta de aceptación registrada correctamente',
            'carta' => $carta
        ], 201);
    }

    /**
     *  Guarda la carta de aceptación con archivo en el storage.
     */
    public function registrarCartaConArchivo(Request $request)
    {
        $request->validate([
            'estadia_id' => 'required|integer|exists:estadias,id',
            'fecha_recepcion' => 'required|date',
            'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // preguntar a charly
            'observaciones' => 'nullable|string',
        ]);

        // Guardar el archivo 
        $rutaArchivo = $request->file('documento')->store('cartas_aceptacion', 'public');

        // Crear el registro con la ruta del archivo guardado
        $carta = CartaAceptacion::create([
            'estadia_id' => $request->estadia_id,
            'fecha_recepcion' => $request->fecha_recepcion,
            'ruta_documento' => $rutaArchivo,
            'observaciones' => $request->observaciones,
        ]);

        return response()->json([
            'mensaje' => 'Carta guardada correctamente',
            'carta' => $carta,
        ], 201);
    }

    /**
     *  Devuelve la carta de aceptación vinculada a una estadía (por Request)
     */
    public function obtenerCartaAceptacionPorEstadia(Request $request)
    {
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
        ], 200); // Nombre del objeto y el codigo de operación 200
    }

    /**
     *  Actualiza una carta de aceptación
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:cartas_aceptacion,id',
            'fecha_recepcion' => 'nullable|date',
            'ruta_documento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        $carta = CartaAceptacion::find($request->id); // If pa que se compruebe si existe la carta

        if (!$carta) {
            return response()->json(['mensaje' => 'Carta no encontrada'], 404);
        }

        $carta->update($request->only(['fecha_recepcion', 'ruta_documento', 'observaciones']));

        return response()->json([
            'mensaje' => 'Carta actualizada correctamente',
            'carta' => $carta
        ])->setStatusCode(200); // Codigo de operación 200
    }

    /**
     *  Elimina una carta de aceptación por ID desde request
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:cartas_aceptacion,id'
        ]);

        $carta = CartaAceptacion::find($request->id); // If pa que se compruebe si existe la carta

        if (!$carta) {
            return response()->json(['mensaje' => 'Carta no encontrada'], 404);
        }

        $carta->delete();

        return response()->json(['mensaje' => 'Carta eliminada correctamente']) // Codigo de operación 200
            ->setStatusCode(200);
    }

    /**
     *  Listar todas las cartas
     */
    //Agregar la función pa que liste las cartas sin filtro, el orden del id, cambiar los parametro pa que se reciban lo del request
    public function listarTodas(Request $request)
    {
        $cartas = CartaAceptacion::orderBy('id')->get(); // Orden por id

        return response()->json(['cartas_aceptacion' => $cartas], 200); // Nombre del array y código de operación 200
    }
}

