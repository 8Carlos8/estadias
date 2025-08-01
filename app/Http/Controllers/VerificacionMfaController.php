<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Models\verificacion_mfa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\TwilioService;
use App\Notifications\VerifyEmailNotification;

class VerificacionMFAController extends Controller
{
    public function __construct(TwilioService $correoService)
    {
        $this->correoService = $correoService;
    }

    // generarCodigoMFA($usuario_id, $metodo), agregar la logica del envio a los metodos
    public function generarCodigo(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required',
            'metodo' => 'required|in:correo,whatsapp',
        ]);

        //Aceder al usuario
        $usuario = Usuario::find($request->usuario_id);

        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $validoHasta = Carbon::now()->addMinutes(5); // Código válido por 5 min

        $verificacion = verificacion_mfa::create([
            'usuario_id' => $request->usuario_id,
            'codigo_enviado' => $codigo,
            'metodo' => $request->metodo,
            'valido_hasta' => $validoHasta,
            'verificado' => false,
        ]);

        // Enviar correo con el código de verificación
        if ($request->metodo === 'correo') {
            $usuario->notify(new VerifyEmailNotification($codigo));
        }

        // Enviar por WhatsApp
        if ($request->metodo === 'whatsapp') {
            try {
                $this->correoService->sendSms(
                    'whatsapp:+521'.$usuario->telefono,
                    "Tu código de verificación es: $codigo"
                );
            } catch (\Exception $e) {
                Log::error("Error al enviar WhatsApp: " . $e->getMessage());
                return response()->json(['message' => 'Usuario no encontrado.'], 404);
            }
        }

        

        return response()->json([
            'mensaje' => 'Código MFA generado',
            'codigo' => $codigo,
            'verificacion' => $verificacion
        ], 201);
    }

    // verificarCodigoMFA($usuario_id, $codigo)
    public function verificarCodigo(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'codigo' => 'required|string|size:6'
        ]);

        $verificacion = VerificacionMFA::where('usuario_id', $request->usuario_id)
            ->where('codigo_enviado', $request->codigo)
            ->where('verificado', false)
            ->where('valido_hasta', '>', now())
            ->latest()
            ->first();

        if (!$verificacion) {
            return response()->json(['mensaje' => 'Código inválido o expirado'], 400);
        }

        $verificacion->verificado = true;
        $verificacion->save();

     return response()->json(['mensaje' => 'Código verificado correctamente'])->setStatusCode(200);
    }

    // obtenerUltimaVerificacionMFA($usuario_id)
    public function obtenerUltimaVerificacion(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id'
        ]);

        $verificacion = VerificacionMFA::where('usuario_id', $request->usuario_id)
            ->where('verificado', false)
            ->orderBy('valido_hasta', 'desc')
            ->first();

        if (!$verificacion) {
            return response()->json(['mensaje' => 'No hay códigos pendientes'], 404);
        }

        return response()->json($verificacion)->setStatusCode(200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\Usuario';
    }
}
