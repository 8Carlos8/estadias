<?php

namespace App\Http\Controllers;

use App\Models\Cartas_presentacion;
use App\Models\Estadia_seguimiento;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class CartasPresentacionController extends Controller
{
    //Aplicar lo mismo del documento extra aca XDXD
    public function register(Request $request)
    {

        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
            */

        $validator = Validator::make($request->all(), [
            'estadia_id' => 'required|integer',
            'tutor_id' => 'required|integer',
            'fecha_emision' => 'required|date',
            'ruta_documento'=> 'required|file',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Guardar archivo tambien corregir pa que se almacene el nombre del archivo en estecaso de la carta
        $path = $request->file('ruta_documento')->store('cartas', 'public'); // ruta relativa

        $cartaPres = Cartas_presentacion::create([
            'estadia_id' => $request->estadia_id,
            'tutor_id' => $request->tutor_id,
            'fecha_emision' => $request->fecha_emision,
            'ruta_documento' => $path,
            'firmada_director' => false,
        ]);

        // Verificar si ya existe un seguimiento para esa estadía
        $seguimiento = Estadia_seguimiento::where('estadia_id', $request->estadia_id)->first();
        
        if ($seguimiento) {
            $seguimiento->etapa = 'presentacion';
            $seguimiento->estatus = 'pendiente';
            $seguimiento->comentario = 'Carta de presentación creada';
            $seguimiento->fecha_actualizacion = now();
            $seguimiento->actualizado_por = 2; //Cmbiar esto por el campo del id
            $seguimiento->save();
        } else {
            // Si no existe seguimiento, crear uno nuevo
            Estadia_seguimiento::create([
                'estadia_id' => $request->estadia_id,
                'etapa' => 'presentacion',
                'estatus' => 'pendiente',
                'comentario' => 'Seguimiento generado automáticamente al registrar carta',
                'fecha_actualizacion' => now(),
                'actualizado_por' => 2, //Cmbiar esto por el campo del id
            ]);
        }

        $url = asset('storage/' . $path);

        return response()->json(['message' => 'Carta registrada con éxito y seguimiento actualizado', 'cartaPres' => $cartaPres, 'seguimiento' => $seguimiento, 'url_documento' => $url], 201);
    }

    public function update(Request $request)
    {
        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
            */

        $cartaPres = Cartas_presentacion::find($request->input('id'));

        if(!$cartaPres){
            return response()->json(['message' => 'Carta de presentación no encontrada'], 404);
        }

        //Checar si se deja las validaciones de los datos y sino quitar eso y dejar lo demas como estaba XDXD
        /*
        $validator = Validator::make($request->all(), [
            'estadia_id' => 'sometimes|integer',
            'tutor_id' => 'sometimes|integer',
            'fecha_emision' => 'sometimes|date',
            'ruta_documento' => 'sometimes|file', // archivo opcional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
            */

        // Si hay un archivo nuevo, lo almacenamos, comprobar esto ya que creo que no funciona XDXD
        if ($request->hasFile('ruta_documento')) {
            $path = $request->file('ruta_documento')->store('cartas', 'public');
            $cartaPres->ruta_documento = $path;
        }

        // Actualizar otros campos
        if ($request->has('estadia_id')) $cartaPres->estadia_id = $request->estadia_id;
        if ($request->has('tutor_id')) $cartaPres->tutor_id = $request->tutor_id;
        if ($request->has('fecha_emision')) $cartaPres->fecha_emision = $request->fecha_emision;

        $cartaPres->save();

        $url = asset('storage/' . $cartaPres->ruta_documento);

        $cartaPres->update($request->all());
        return response()->json(['message' => 'Carta de presentación actualizada con éxito', 'cartaPres' => $cartaPres, 'url_documento' => $url], 200);
    }

    public function delete(Request $request)
    {
        //Regresar a la otra etapa del seguimiento, aca y en la parte del update y la firma
        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
            */

        $cartaPres = Cartas_presentacion::find($request->input('id'));

        if(!$cartaPres){
            return response()->json(['message' => 'Carta de presentación no encontrada'], 404);
        }

        $cartaPres->delete();
        return response()->json(['message' => 'Carta de presentación eliminada con éxito'], 200);
    }

    public function verCartaPres(Request $request)
    {
        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
            */

        $id = $request->input('id');

        $cartaPres = Cartas_presentacion::find($id);
        if(!$cartaPres){
            return response()->json(['message' => 'Carta de presentación no encontrada'], 404);
        }

        return response()->json(['cartaPres' => $cartaPres], 200);
    }

    public function listaCartasPres(Request $request)
    {
        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
            */

        try {
            $cartasPres = Cartas_presentacion::all();
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener la lista de cartas', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['cartasPres' => $cartasPres], 200);
    }

    public function firmaCartaPres(Request $request)
    {
        /*
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
            */

        $id = $request->input('id');
        $cartaPres = Cartas_presentacion::find($id);

        if(!$cartaPres){
            return response()->json(['message' => 'Carta no encontrada'], 404);
        }

        //Checar si se deja las validaciones de los datos y sino quitar eso y dejar lo demas como estaba XDXD
        /*
        $validator = Validator::make($request->all(), [
            'ruta_documento' => 'sometimes|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
            */

        // Si hay un archivo nuevo, lo almacenamos, comprobar esto ya que creo que no funciona XDXD
        if ($request->hasFile('ruta_documento')) {
            $path = $request->file('ruta_documento')->store('cartas', 'public');
            $cartaPres->ruta_documento = $path;
        }

        $url = asset('storage/' . $cartaPres->ruta_documento);

        //Cambiar el estado en la parte del seguimiento del tramite
        $cartaPres->firmada_director = true;
        $cartaPres->save();

        // Actualizar seguimiento de la estadía
        $seguimiento = Estadia_seguimiento::where('estadia_id', $cartaPres->estadia_id)->first();
        if($seguimiento){
            $seguimiento->etapa = 'firma_director';
            $seguimiento->estatus = 'completado';
            $seguimiento->comentario = 'Carta de presentación creada y firmada por el director';
            $seguimiento->fecha_actualizacion = now();
            $seguimiento->save();
        }

        return response()->json(['message' => 'Carta firmada por el director y seguimiento actualizado', 'cartaPres' => $cartaPres, 'seguimiento' => $seguimiento, 'url_documento' => $url], 200);
    }

    public function descargarCartaPres(Request $request)
    {
        /*Logida de la descarga de la carga con la libreria 
        que utilizo meli en la bitacora o buscar de otra manera la descarga,
        pero creo que es la ruta del doc, devolverla y que la abra en una pestaña nueva
        y que la descargue y tambiem agregar el boton de descarga desde afuera
        
        $token = $request->input('token');
        if(!$this->validateToken($token)){
            return response()->json(['message' => 'Token inválido'], 401);
        }
        */

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
