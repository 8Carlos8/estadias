<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected $token;
    protected $phoneId;
    protected $version;

    public function __construct()
    {
        $this->token = "EAAH9yXswThUBO1NaZCQ94PgN45Tym9G8DRq4a6PGTOdkvieaqffzHtuLfjJzzpsE36vwIwECHQOXZCwelNv5x0pvcrK9XFEptl9eTs4P6LJXDJnyFTMfVGncd4zg6OhHZCPcXZAfnNeCILUovSR2MoPiKuZClXiURxQeloc1mKzvKMYZAlFkxpUsEfmQJE7S6q10OiG0iWwgDtAv4UpPQTT4MqWtETFZCvb6B7DsSaNcOgZD";
        $this->phoneId = "664175743427160";
        $this->version = "v22.0";
    }

    public function sendMessage($to, $message)
    {
        $url = "https://graph.facebook.com/v22.0/{$this->phoneId}/messages";

        $response = Http::withToken($this->token)->post($url, [
            "messaging_product" => "whatsapp",
            "to" => $to,
            "type" => "text", // Asegurándonos de que sea un mensaje de texto
            "text" => [
                "body" => $message
            ]
        ]);

        // Registrar la respuesta completa para depurar
        Log::info('Respuesta de la API de WhatsApp: ', $response->json());

        // Verificar si hubo un error en la respuesta
        if ($response->failed()) {
            Log::error('Error en la API de WhatsApp: ', $response->json());
        }
        
        return $response->json();
    }
}
