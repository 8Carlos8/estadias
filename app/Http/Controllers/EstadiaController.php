<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Estadia;
use App\Models\Estadia_seguimiento;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;

class EstadiaController extends Controller
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
            'alumno_id' => 'required|integer',
            'empresa_id' => 'required|integer',
            'asesor_externo' => 'required|string',
            'proyecto_nombre' => 'required|string',
            'duracion_semanas' => 'required|integer',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'apoyo' => 'required|string',
        ]);

        //Si la validación falla, devolver un error
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $estadia = Estadia::create([
            'alumno_id' => $request->alumno_id,
            'id_docente' => $usuario->id,
            'empresa_id' => $request->empresa_id,
            'asesor_externo' => $request->asesor_externo,
            'proyecto_nombre' => $request->proyecto_nombre,
            'duracion_semanas' => $request->duracion_semanas,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'apoyo' => $request->apoyo,
            'estatus' => 'solicitada',
        ]);

        // 2. Crear seguimiento inicial
        Estadia_seguimiento::create([
            'estadia_id' => $estadia->id,
            'etapa' => 'solicitud',
            'estatus' => 'pendiente',
            'comentario' => 'Seguimiento inicial generado automáticamente',
            'fecha_actualizacion' => now(),
            'actualizado_por' => $usuario->id, //Cmbiar esto por el campo del id
        ]);

        //Obtener el token del wearable
        $userTokenWearable = Usuario::find(1);
        if($userTokenWearable && $userTokenWearable->device_token){
            $mensaje = "Se ha registrado una nueva estadía con proyecto: " . $request->proyecto_nombre;
            $this->enviarNotificacionFCM($userTokenWearable->device_token, $mensaje);
        }

        return response()->json(['message' => 'Estadia registrada con éxito y seguimiento inicial creado', 'estadia' => $estadia], 201);
    }

    public function update(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $estadia = Estadia::find($request->input('id'));

        if(!$estadia){
            return response()->json(['message' => 'Estadia no encontrada'], 404);
        }

        $estadia->update($request->all());
        return response()->json(['message' => 'Estadia actualizada con éxito' , 'estadia' => $estadia], 200);
    }

    public function delete(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $estadia = Estadia::find($request->input('id'));

        if(!$estadia){
            return response()->json(['message' => 'Estadia no encontrada'], 404);
        }

        $estadia->delete();
        return response()->json(['message' => 'Estadia eliminada con éxito'], 200);
    }

    public function verEstadia(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');

        $estadia = Estadia::find($id);
        if(!$estadia){
            return response()->json(['message' => 'Estadia no encontrada'], 404);
        }

        return response()->json(['estadia' => $estadia], 200);
    }

    public function listaEstadias(Request $request)
    {
        
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        try {
            $estadias = Estadia::all();
        } catch (Exception $e) {
            return reponse()->json(['message' => 'Error al obtener la lista de estadias', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['estdias' => $estadias], 200);
    }

    public function estadiasPorDocente(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        // Obtener el usuario asociado al token
        $usuario = $accessToken->tokenable;

        try {
            // Buscar todas las estadias del docente logueado
            $estadias = Estadia::where('id_docente', $usuario->id)->get();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las estadías',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json(['estadias' => $estadias], 200);
    }

    public function contarEstadias(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        // Contar estadías
        $count = Estadia::count();

        return response()->json(['total_estadias' => $count], 200);
    }

    public function contarEstadiasDocente(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $usuario = $accessToken->tokenable;

        // Contar estadías
        $count = Estadia::where('id_docente', $usuario->id)->count();

        return response()->json(['total_estadias' => $count], 200);
    }

    public function alumnosPorDocente(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $usuario = $accessToken->tokenable;

        // Obtener IDs únicos de alumnos en las estadías del docente
        $alumnoIds = Estadia::where('id_docente', $usuario->id)
            ->pluck('alumno_id')
            ->unique();

        // Obtener datos de los alumnos
        $alumnos = Usuario::whereIn('id', $alumnoIds)->get(['id', 'nombre', 'correo']);

        return response()->json(['alumnos' => $alumnos], 200);
    }

    private function enviarNotificacionFCM($deviceToken, $mensaje)
    {
        try {
            // Carga las credenciales de Firebase
            $firebase = (new Factory)->withServiceAccount(base_path('prueba-de-wearable-firebase-adminsdk-fbsvc-ba10fc00af.json'));
            $messaging = $firebase->createMessaging();

            // Construye el mensaje para FCM
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification([
                    'title' => 'Actualización de Estadía', // Título que se mostrará en el wearable
                    'body' => $mensaje, // El mensaje que se mostrará
                ]);

            // Envía el mensaje
            $messaging->send($message);
            Log::info("Notificación FCM enviada con éxito al token: $deviceToken");
        } catch (\Exception $e) {
            Log::error("Error al enviar notificación FCM: " . $e->getMessage());
        }
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\Usuario';
    }
}
