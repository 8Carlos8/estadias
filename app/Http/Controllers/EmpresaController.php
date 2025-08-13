<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use Laravel\Sanctum\PersonalAccessToken;

class EmpresaController extends Controller
{
    public function verEmpresa(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');

        $empresa = Empresa::find($id);
        if(!$empresa){
            return response()->json(['message' => 'Empresa no encontrada'], 404);
        }

        return response()->json(['empresa' => $empresa], 200);
    }

    public function listaEmpresas(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        try {
            $empresas = Empresa::all();
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la lista de empresas', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['Empresas' => $empresas], 200);
    }

    public function contarEmpresas(Request $request)
    {
        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if(!$accessToken){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $count = Empresa::count();

        return response()->json(['total_empresas' => $count], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\Usuario';
    }
}
