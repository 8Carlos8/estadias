<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificacion;

class NotificacionController extends Controller
{
    // ● crearNotificacion($datos)
    public function crearNotificacion(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'tipo' => 'required|in:correo,sms,wearable,app',
            'mensaje' => 'required|string',
            'fecha_envio' => 'required|date',
            'prioridad' => 'required|in:alta,media,baja',
        ]);

        $notificacion = Notificacion::create([
            'usuario_id' => $request->usuario_id,
            'tipo' => $request->tipo,
            'mensaje' => $request->mensaje,
            'fecha_envio' => $request->fecha_envio,
            'prioridad' => $request->prioridad,
            'leida' => false
        ]);

        return response()->json([
            'mensaje' => 'Notificación creada correctamente',
            'notificacion' => $notificacion
        ], 201);
    }

    // ● obtenerNotificacionesUsuario($usuario_id)
    public function obtenerNotificacionesUsuario($usuario_id)
    {
        $notificaciones = Notificacion::where('usuario_id', $usuario_id)->orderByDesc('fecha_envio')->get();

        return response()->json($notificaciones);
    }

    // ● marcarNotificacionLeida($notificacion_id)
    public function marcarNotificacionLeida($id)
    {
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