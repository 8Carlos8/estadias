<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CartaAceptacionController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\EstadiaController;
use App\Http\Controllers\CartasPresentacionController;
use App\Http\Controllers\EstadiaSeguimientoController;
use App\Http\Controllers\VerificacionDocumentoController;
use App\Http\Controllers\RegistrarIncidenciaController;
use App\Http\Controllers\AgregarDocumentoExtraController;
use App\Http\Controllers\ProgramarVisitaController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

//Usuarios 
Route::prefix('usuarios')->group(function () {
    Route::post('/registrar', [UsuarioController::class, 'registrarUsuario']);
    Route::post('/login', [UsuarioController::class, 'iniciarSesion']);
    Route::post('/por-correo', [UsuarioController::class, 'obtenerUsuarioPorCorreo']);
    Route::post('/actualizar-password', [UsuarioController::class, 'actualizarPassword']);
    Route::post('/activar-mfa', [UsuarioController::class, 'activarMFA']);
});

//Cartas de aceptación
Route::prefix('cartas')->group(function () {
    //Corregir las demas rutas
    Route::post('/registrar', [CartaAceptacionController::class, 'registrarCartaAceptacion']);
    Route::get('/por-estadia/{estadia_id}', [CartaAceptacionController::class, 'obtenerCartaAceptacionPorEstadia']);
    Route::put('/{id}', [CartaAceptacionController::class, 'update']);
    Route::delete('/{id}', [CartaAceptacionController::class, 'destroy']);
});


//Notificaciones
Route::prefix('notificaciones')->group(function () {
    Route::post('/crear', [NotificacionController::class, 'crearNotificacion']);
    Route::get('/usuario/{usuario_id}', [NotificacionController::class, 'obtenerNotificacionesUsuario']);
    Route::put('/marcar-leida/{id}', [NotificacionController::class, 'marcarNotificacionLeida']);
});

//Rutas de Estadia
Route::post('/estadia/register', [EstadiaController::class, 'register']);
Route::post('/estadia/update', [EstadiaController::class, 'update']);
Route::post('/estadia/delete', [EstadiaController::class, 'delete']);
Route::post('/estadia/verEstadia', [EstadiaController::class, 'verEstadia']);
Route::post('/estadia/listaEstadias', [EstadiaController::class, 'listaEstadias']);

//Rutas de Incidencia
Route::post('/incidencia/register', [RegistrarIncidenciaController::class, 'register']);
Route::post('/incidencia/update', [RegistrarIncidenciaController::class, 'update']);
Route::post('/incidencia/delete', [RegistrarIncidenciaController::class, 'delete']);
Route::post('/incidencia/verIncidencia', [RegistrarIncidenciaController::class, 'verIncidencia']);
Route::post('/incidencia/listaIncidencias', [RegistrarIncidenciaController::class, 'listaIncidencias']);

//Rutas de Documento Extra
Route::post('/docExtra/register', [AgregarDocumentoExtraController::class, 'register']);
Route::post('/docExtra/update', [AgregarDocumentoExtraController::class, 'update']);
Route::post('/docExtra/delete', [AgregarDocumentoExtraController::class, 'delete']);
Route::post('/docExtra/verDocExtra', [AgregarDocumentoExtraController::class, 'verDocExtra']);
Route::post('/docExtra/listaDocExtra', [AgregarDocumentoExtraController::class, 'listaDocExtra']);

//Rutas de Programar Visitas
Route::post('/visita/register', [ProgramarVisitaController::class, 'register']);
Route::post('/visita/update', [ProgramarVisitaController::class, 'update']);
Route::post('/visita/delete', [ProgramarVisitaController::class, 'delete']);
Route::post('/visita/verVisita', [ProgramarVisitaController::class, 'verVisita']);
Route::post('/visita/listaVisitas', [ProgramarVisitaController::class, 'listaVisitas']);

//Rutas de Carta Presentación
Route::post('/cartaPres/register', [CartasPresentacionController::class, 'register']);
Route::post('/cartaPres/update', [CartasPresentacionController::class, 'update']);
Route::post('/cartaPres/delete', [CartasPresentacionController::class, 'delete']);
Route::post('/cartaPres/verCartaPres', [CartasPresentacionController::class, 'verCartaPres']);
Route::post('/cartaPres/listaCartasPres', [CartasPresentacionController::class, 'listaCartasPres']);
Route::post('/cartaPres/firmaCartaPres', [CartasPresentacionController::class, 'firmaCartaPres']);
Route::post('/cartaPres/descargarCartaPres', [CartasPresentacionController::class, 'descargarCartaPres']);

//Rutas de Seguimiento de Estadia
Route::post('/segEstadia/register', [EstadiaSeguimientoController::class, 'register']);
Route::post('/segEstadia/update', [EstadiaSeguimientoController::class, 'update']);
Route::post('/segEstadia/delete', [EstadiaSeguimientoController::class, 'delete']);
Route::post('/segEstadia/verSeguimiento', [EstadiaSeguimientoController::class, 'verSeguimiento']);
Route::post('/segEstadia/listaSeguimientos', [EstadiaSeguimientoController::class, 'listaSeguimientos']);

//Rutas de Verificación de Documentos
Route::post('/verifi/register', [VerificacionDocumentoController::class, 'register']);
Route::post('/verifi/update', [VerificacionDocumentoController::class, 'update']);
Route::post('/verifi/delete', [VerificacionDocumentoController::class, 'delete']);
Route::post('/verifi/verVerificacion', [VerificacionDocumentoController::class, 'verVerificacion']);
Route::post('/verifi/listaVerificaciones', [VerificacionDocumentoController::class, 'listaVerificaciones']);
Route::post('/verifi/VerificacionesUsuario', [VerificacionDocumentoController::class, 'VerificacionesUsuario']);