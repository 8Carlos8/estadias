<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartaAceptacion;

class CartaAceptacionController extends Controller
{
    //listar todos las cartas
    /**
     * Guarda la carta de aceptación recibida por parte de la empresa.
     * Equivalente a: registrarCartaAceptacion($datos)
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
     * Devuelve la carta de aceptación vinculada a una estadía.
     * Equivalente a: obtenerCartaAceptacionPorEstadia($estadia_id), cambiar los parametro pa que se reciban lo del request
     */
    public function obtenerCartaAceptacionPorEstadia($estadia_id)
    {
        //Agregar la parte de la verificación del token pa acceder a las funciones
        //Agregar la parte del input para que ahi se haga la consulta
        $carta = CartaAceptacion::where('estadia_id', $estadia_id)->first();

        if (!$carta) {
            return response()->json([
                'mensaje' => 'No se encontró ninguna carta para la estadía indicada.'
            ], 404);
        }

        //Agregar el nombre al objeto
        return response()->json($carta, 200);
    }

    /**
     * Actualiza una carta de aceptación por su ID. quitar el parametro id
     */
    public function update(Request $request, $id)
    {
        //Agregar la parte de la verificación del token pa acceder a las funciones
        //Agregar la parte del input para que ahi se haga la consulta
        $carta = CartaAceptacion::find($id);

        if (!$carta) {
            return response()->json(['mensaje' => 'Carta no encontrada'], 404);
        }

        $request->validate([
            'fecha_recepcion' => 'date',
            'ruta_documento' => 'string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        $carta->update($request->all());

        return response()->json([
            'mensaje' => 'Carta actualizada correctamente',
            'carta' => $carta
        ]);
    }

    /**
     * Elimina una carta de aceptación por su ID. cambiar los parametro pa que se reciban lo del request
     */
    public function destroy($id)
    {
        //Agregar la parte de la verificación del token pa acceder a las funciones
        //Agregar la parte del input para que ahi se haga la consulta
        $carta = CartaAceptacion::find($id);

        if (!$carta) {
            return response()->json(['mensaje' => 'Carta no encontrada'], 404);
        }

        $carta->delete();

        return response()->json(['mensaje' => 'Carta eliminada correctamente']);
    }
}

