<?php
//CI 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CartaAceptacionController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\VerificacionMFAController;


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

// Usuarios

Route::prefix('usuarios')->group(function () {
    Route::post('/registrar', [UsuarioController::class, 'registrarUsuario']);
    Route::post('/login', [UsuarioController::class, 'iniciarSesion']);
    Route::post('/por-correo', [UsuarioController::class, 'obtenerUsuarioPorCorreo']);
    Route::post('/por-id', [UsuarioController::class, 'obtenerUsuarioPorId']);
    Route::post('/actualizar', [UsuarioController::class, 'actualizarUsuario']);
    Route::post('/actualizar-password', [UsuarioController::class, 'actualizarPassword']);
    Route::post('/activar-mfa', [UsuarioController::class, 'activarMFA']);
    Route::post('/eliminar', [UsuarioController::class, 'eliminarUsuario']);
    Route::post('/listar', [UsuarioController::class, 'listarUsuarios']);
});


// Cartas de Aceptación

Route::prefix('cartas')->group(function () {
    Route::post('/registrar', [CartaAceptacionController::class, 'registrarCartaAceptacion']);
    Route::post('/por-estadia', [CartaAceptacionController::class, 'obtenerCartaAceptacionPorEstadia']);
    Route::post('/actualizar', [CartaAceptacionController::class, 'update']);
    Route::post('/eliminar', [CartaAceptacionController::class, 'destroy']);
    Route::post('/listar', [CartaAceptacionController::class, 'listarTodas']);
});


// Notificaciones

Route::prefix('notificaciones')->group(function () {
    Route::post('/crear', [NotificacionController::class, 'crearNotificacion']);
    Route::post('/usuario', [NotificacionController::class, 'obtenerNotificacionesUsuario']);
    Route::post('/marcar-leida', [NotificacionController::class, 'marcarNotificacionLeida']);
    Route::post('/actualizar', [NotificacionController::class, 'actualizarNotificacion']); 
    Route::post('/eliminar', [NotificacionController::class, 'eliminarNotificacion']);     
    Route::post('/listar', [NotificacionController::class, 'listarNotificaciones']);       
});

//Verificación mfa

Route::prefix('mfa')->group(function () {
    Route::post('/generar', [VerificacionMFAController::class, 'generarCodigo']);
    Route::post('/verificar', [VerificacionMFAController::class, 'verificarCodigo']);
    Route::post('/pendiente', [VerificacionMFAController::class, 'obtenerUltimaVerificacion']);
    Route::post('/listar', [VerificacionMFAController::class, 'listar']);
});