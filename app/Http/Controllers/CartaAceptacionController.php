<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartaAceptacion;
use App\Models\Estadia;

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

        return response()->json($carta); //Nombre del objeto y el codigo de operacioón
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

        $carta = CartaAceptacion::find($request->id);//If pa que se compruebe si existe la carta

        $carta->update($request->only(['fecha_recepcion', 'ruta_documento', 'observaciones']));

        return response()->json([
            'mensaje' => 'Carta actualizada correctamente',
            'carta' => $carta 
        ]);//Codigo de operación 200
    }

    /**
     *  Elimina una carta de aceptación por ID desde request
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:cartas_aceptacion,id'
        ]);

        $carta = CartaAceptacion::find($request->id); //If pa que se compruebe si existe la carta
        $carta->delete();

        return response()->json(['mensaje' => 'Carta eliminada correctamente']); //Codigo de operación
    }
    /**
     *  Listar todas las cartas
     */
    //Agregar la función pa que liste las cartas sin filtro, el orden del id, cambiar los parametro pa que se reciban lo del request
    public function listarTodas()
    {
        $cartas = CartaAceptacion::orderByDesc('fecha_recepcion')->get();

        return response()->json($cartas); //Nombre del array
    }

}

