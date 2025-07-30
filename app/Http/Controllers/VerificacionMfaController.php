<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VerificacionMFA;
use Carbon\Carbon;

class VerificacionMFAController extends Controller
{
    // generarCodigoMFA($usuario_id, $metodo), agregar la logica del envio a los metodos
    public function generarCodigo(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'metodo' => 'required|in:correo,whatsapp', //whatsapp y correo
        ]);

        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $validoHasta = Carbon::now()->addMinutes(5); // Código válido por 5 min

        $verificacion = VerificacionMFA::create([
            'usuario_id' => $request->usuario_id,
            'codigo_enviado' => $codigo,
            'metodo' => $request->metodo,
            'valido_hasta' => $validoHasta,
            'verificado' => false,
        ]);

        return response()->json([
            'mensaje' => 'Código MFA generado',
            'codigo' => $codigo,
            'verificacion' => $verificacion
    ], 201); // Codigo de operación 201
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

     return response()->json(['mensaje' => 'Código verificado correctamente'])->setStatusCode(200); //Codigo de operación 200
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

        return response()->json($verificacion)->setStatusCode(200);//Codigo de operación 200
    }
}
