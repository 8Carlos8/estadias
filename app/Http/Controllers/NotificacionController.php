<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificacion;

class NotificacionController extends Controller
{
    //Agregar el actualizar, eliminar, buscar por id y listar todas las notificaciones
    // ● crearNotificacion($datos)
    public function crearNotificacion(Request $request)
    {
        //Agregar la parte de la verificación del token pa acceder a las funciones
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'tipo' => 'required|in:correo,sms,wearable,app',
            'mensaje' => 'required|string',
            'fecha_envio' => 'required|date',
            'prioridad' => 'required|in:alta,media,baja',//Checar si es con numero o con el nombre del rol
        ]);

        $notificacion = Notificacion::create([
            'usuario_id' => $request->usuario_id,
            'tipo' => $request->tipo,
            'mensaje' => $request->mensaje,
            'fecha_envio' => $request->fecha_envio,
            'prioridad' => $request->prioridad,
            'leida' => false,
        ]);

        return response()->json([
            'mensaje' => 'Notificación creada correctamente',
            'notificacion' => $notificacion
        ], 201);
    }

    // ● obtenerNotificacionesUsuario($usuario_id) cambiar el parametro pa que reciba los request
    public function obtenerNotificacionesUsuario($usuario_id)
    {
        //Agregar la parte de la verificación del token pa acceder a las funciones
        //Agregar la parte del input para que ahi se haga la consulta
        $notificaciones = Notificacion::where('usuario_id', $usuario_id)->orderByDesc('fecha_envio')->get();

        //Validación si existe la notificación (if)

        //Agregar el nombre al objeto
        return response()->json($notificaciones);
    }

    // ● marcarNotificacionLeida($notificacion_id) cambiar el parametro pa que reciba los request
    public function marcarNotificacionLeida($id)
    {
        //Agregar la parte de la verificación del token pa acceder a las funciones
        //Agregar la parte del input para que ahi se haga la consulta
        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return response()->json(['mensaje' => 'Notificación no encontrada'], 404);
        }

        $notificacion->update(['leida' => true]);

        return response()->json([
            'mensaje' => 'Notificación marcada como leída',
            'notificacion' => $notificacion
        ]);
    }
}