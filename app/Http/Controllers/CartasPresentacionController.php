<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartasPresentacionController extends Controller
{
    public function register(Request $request)
    {

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $validator = Validator::make($request->all(), [
            'estadia_id' => 'required|integer',
            'tutor_id' => 'required|integer',
            'fecha_emision' => 'required|string',
            'ruta_documento'=> 'required|string',
            'firmada_director' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $cartaPres = Cartas_presentacion::create([
            'estadia_id' => $request->estadia_id,
            'tutor_id' => $request->tutor_id,
            'fecha_emision' => $request->fecha_emision,
            'ruta_documento' => $request->ruta_documento,
            'firmada_director' => $request->firmada_director,
        ]);

        return response()->json(['message' => 'Carta registrada con éxito', 'cartaPres' => $cartaPres], 201);
    }

    public function update(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $cartaPres = Cartas_presentacion::find($request->input('id'));

        if(!$cartaPres){
            return response()->json(['message' => 'Carta de presentación no encontrada'], 404);
        }

        $cartaPres->update($request->all());
        return response()->json(['message' => 'Carta de presentación actualizada con éxito', 'cartaPres' => $cartaPres], 200);
    }

    public function delete(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $cartaPres = Cartas_presentacion::find($request->input('id'));

        if(!$cartaPres){
            return response()->json(['message' => 'Carta de presentación no encontrada'], 404);
        }

        $cartaPres->delete();
        return response()->json(['message' => 'Carta de presentación eliminada con éxito'], 200);
    }

    public function verCartaPres(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');

        $cartaPres = Cartas_presentacion::find($id);
        if(!$cartaPres){
            return response()->json(['message' => 'Carta de presentación no encontrada'], 404);
        }

        return response()->json(['cartaPres' => $cartaPres], 200);
    }

    public function listaCartasPres(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        try {
            $cartasPres = Cartas_presentacion::all();
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la lista de cartas', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['cartasPres' => $cartasPres], 200);
    }

    public function firmaCartaPres(Request $request)
    {
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');
        $cartaPres = Cartas_presentacion::find($request->input($id));

        if(!$cartaPres){
            return response()->json(['message' => 'Carta no encontrada'], 404);
        }

        //Cambiar el estadp en la parte del seguimiento del tramite
        $cartaPres->firmada_director = true;
        $cartaPres->save();

        return response()->json(['message' => 'Carta firmada por el director', 'cartaPres' => $cartaPres], 200);
    }

    public function descargarCartaPres(Request $request)
    {
        /*Logida de la descarga de la carga con la libreria 
        que utilizo meli en la bitacora o buscar de otra manera la descarga,
        pero creo que es la ruta del doc, devolverla y que la abra en una pestaña nueva
        y que la descargue y tambiem agregar el boton de descarga desde afuera
        */

        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $id = $request->input('id');
        $cartaPres = Cartas_presentacion::find($id);

        if(!$cartaPres){
            return response()->json(['message' => 'Carta no encontrada'], 404);
        }

        //Checar la ruta del pdf 
        $ruta = $cartaPres->ruta_documento;
        $url = asset('storage/' . $ruta);

        return response()->json(['url_descarga' => $url], 200);
    }

    private function validateToken($token)
    {
        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken && $accessToken->tokenable_type === 'App\Models\User';
    }
}
