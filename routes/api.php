<?php
//CI 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CartaAceptacionController;
use App\Http\Controllers\NotificacionController;


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