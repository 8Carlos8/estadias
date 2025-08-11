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

        return response()->json($notificaciones); //Nombre del objeto y código
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

    //  Buscar notificación por ID, cambiar los parametro pa que se reciban lo del request
    public function obtenerNotificacionPorId(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:notificaciones,id',
        ]);

        $notificacion = Notificacion::find($request->id);

        if (!$notificacion) {
            return response()->json(['mensaje' => 'Notificación no encontrada'], 404);
        }

        return response()->json([
            'notificacion' => $notificacion
        ], 200);
    }

    //  Actualizar notificación, quitar el parámetro id
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:notificaciones,id',
            'tipo' => 'in:correo,sms,wearable,app',
            'mensaje' => 'string|nullable',
            'fecha_envio' => 'date|nullable',
            'prioridad' => 'in:alta,media,baja',
            'leida' => 'boolean'
        ]);

        $notificacion = Notificacion::find($request->id);

        if (!$notificacion) {
            return response()->json(['mensaje' => 'Notificación no encontrada'], 404);
        }

        $notificacion->update($request->only([
            'tipo', 'mensaje', 'fecha_envio', 'prioridad', 'leida'
        ]));

        return response()->json([
            'mensaje' => 'Notificación actualizada',
            'notificacion' => $notificacion
        ], 200);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:notificaciones,id',
        ]);

        $notificacion = Notificacion::find($request->id);

        if (!$notificacion) {
            return response()->json(['mensaje' => 'Notificación no encontrada'], 404);
        }

        $notificacion->delete();

        return response()->json(['mensaje' => 'Notificación eliminada'], 200);
    }
// Cambio aqui
    //  Listar todas las notificaciones, cambiar los parametro pa que se reciban lo del request, listar las notificaciones por id
    public function listarTodas(Request $request) 
{
    $request->validate([
        'token' => 'required|string',
        'id' => 'nullable|integer|exists:notificaciones,id',
    ]);

    // Validar el token
    if (!$this->validateToken($request->token)) {
        return response()->json(['mensaje' => 'Token inválido'], 401);
    }

    // Si se pasó un ID, buscar solo esa notificación
    if ($request->filled('id')) {
        $notificacion = Notificacion::find($request->id);

        if (!$notificacion) {
            return response()->json(['mensaje' => 'Notificación no encontrada'], 404);
        }

        return response()->json(['notificaciones' => [$notificacion]], 200);
    }

    // Si no se pasó ID, listar todas
    $notificaciones = Notificacion::orderByDesc('fecha_envio')->get();

    return response()->json(['notificaciones' => $notificaciones], 200);
}
}