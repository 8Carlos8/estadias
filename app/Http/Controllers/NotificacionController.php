<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificacion;
use App\Models\Usuario;

class NotificacionController extends Controller
{

    //  Crear notificación
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
            'leida' => false,
        ]);

        return response()->json([
            'mensaje' => 'Notificación creada correctamente',
            'notificacion' => $notificacion
        ], 201);
    }

    //  Obtener notificaciones por usuario desde request
    public function obtenerNotificacionesUsuario(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id'
        ]);

        $notificaciones = Notificacion::where('usuario_id', $request->usuario_id)
            ->orderByDesc('fecha_envio')
            ->get();

        if ($notificaciones->isEmpty()) {
            return response()->json(['mensaje' => 'No se encontraron notificaciones para el usuario.'], 404);
        }

        // Agregar nombre del usuario a cada notificación 
        $usuario = Usuario::find($request->usuario_id);
        $notificaciones->each(function ($notificacion) use ($usuario) {
            $notificacion->nombre_usuario = $usuario->nombre . ' ' . $usuario->apellido_paterno;
        });

        return response()->json($notificaciones);
    }

    // Marcar notificación como leída
    public function marcarNotificacionLeida(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:notificaciones,id'
        ]);

        $notificacion = Notificacion::find($request->id);
        $notificacion->leida = true;
        $notificacion->save();

        return response()->json([
            'mensaje' => 'Notificación marcada como leída',
            'notificacion' => $notificacion
        ]);
    }

    //  Buscar notificación por ID
    public function obtenerNotificacionPorId($id)
    {
        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return response()->json(['mensaje' => 'Notificación no encontrada'], 404);
        }

        return response()->json($notificacion);
    }

    //  Actualizar notificación
    public function update(Request $request, $id)
    {
        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return response()->json(['mensaje' => 'Notificación no encontrada'], 404);
        }

        $request->validate([
            'tipo' => 'in:correo,sms,wearable,app',
            'mensaje' => 'string|nullable',
            'fecha_envio' => 'date|nullable',
            'prioridad' => 'in:alta,media,baja',
            'leida' => 'boolean'
        ]);

        $notificacion->update($request->only([
            'tipo', 'mensaje', 'fecha_envio', 'prioridad', 'leida'
        ]));

        return response()->json([
            'mensaje' => 'Notificación actualizada',
            'notificacion' => $notificacion
        ]);
    }

    //  Eliminar notificación
    public function destroy($id)
    {
        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return response()->json(['mensaje' => 'Notificación no encontrada'], 404);
        }

        $notificacion->delete();

        return response()->json(['mensaje' => 'Notificación eliminada']);
    }

    //  Listar todas las notificaciones
    public function listarTodas()
    {
        $notificaciones = Notificacion::orderByDesc('fecha_envio')->get();

        return response()->json($notificaciones);
    }
}
