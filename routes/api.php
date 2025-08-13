<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CartaAceptacionController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\VerificacionMFAController;
use App\Http\Controllers\EstadiaController;
use App\Http\Controllers\CartasPresentacionController;
use App\Http\Controllers\EstadiaSeguimientoController;
use App\Http\Controllers\VerificacionDocumentoController;
use App\Http\Controllers\RegistrarIncidenciaController;
use App\Http\Controllers\AgregarDocumentoExtraController;
use App\Http\Controllers\ProgramarVisitaController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\CartaTerminacionController;


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
    Route::post('/logout', [UsuarioController::class, 'logout']);
    Route::post('/por-correo', [UsuarioController::class, 'obtenerUsuarioPorCorreo']);
    Route::post('/por-id', [UsuarioController::class, 'obtenerUsuarioPorId']);
    Route::post('/actualizar', [UsuarioController::class, 'actualizarUsuario']);
    Route::post('/actualizar-password', [UsuarioController::class, 'actualizarPassword']);
    Route::post('/activar-mfa', [UsuarioController::class, 'activarMFA']);
    Route::post('/eliminar', [UsuarioController::class, 'eliminarUsuario']);
    Route::post('/listar', [UsuarioController::class, 'listarUsuarios']);
    Route::post('/actualizarTokenFCM', [UsuarioController::class, 'actualizarTokenFCM']);
    Route::post('/contarAlumnos', [UsuarioController::class, 'contarAlumnos']);
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

//Rutas de Estadia
Route::prefix('estadia')->group(function (){
    Route::post('/register', [EstadiaController::class, 'register']);
    Route::post('/update', [EstadiaController::class, 'update']);
    Route::post('/delete', [EstadiaController::class, 'delete']);
    Route::post('/verEstadia', [EstadiaController::class, 'verEstadia']);
    Route::post('/listaEstadias', [EstadiaController::class, 'listaEstadias']);
    Route::post('/estadiasPorDocente', [EstadiaController::class, 'estadiasPorDocente']);
    Route::post('/contarEstadiasDocente', [EstadiaController::class, 'contarEstadiasDocente']);
    Route::post('/estadiasPorDocente', [EstadiaController::class, 'estadiasPorDocente']);
    Route::post('/contarEstadias', [EstadiaController::class, 'contarEstadias']);
});

//Rutas de Incidencia
Route::prefix('incidencia')->group(function (){
    Route::post('/register', [RegistrarIncidenciaController::class, 'register']);
    Route::post('/update', [RegistrarIncidenciaController::class, 'update']);
    Route::post('/delete', [RegistrarIncidenciaController::class, 'delete']);
    Route::post('/verIncidencia', [RegistrarIncidenciaController::class, 'verIncidencia']);
    Route::post('/listaIncidencias', [RegistrarIncidenciaController::class, 'listaIncidencias']);
});

//Rutas de Documento Extra
Route::prefix('docExtra')->group(function (){
    Route::post('/register', [AgregarDocumentoExtraController::class, 'register']);
    Route::post('/update', [AgregarDocumentoExtraController::class, 'update']);
    Route::post('/delete', [AgregarDocumentoExtraController::class, 'delete']);
    Route::post('/verDocExtra', [AgregarDocumentoExtraController::class, 'verDocExtra']);
    Route::post('/listaDocExtra', [AgregarDocumentoExtraController::class, 'listaDocExtra']);
});

//Rutas de Programar Visitas
Route::prefix('visita')->group(function (){
    Route::post('/register', [ProgramarVisitaController::class, 'register']);
    Route::post('/update', [ProgramarVisitaController::class, 'update']);
    Route::post('/delete', [ProgramarVisitaController::class, 'delete']);
    Route::post('/verVisita', [ProgramarVisitaController::class, 'verVisita']);
    Route::post('/listaVisitas', [ProgramarVisitaController::class, 'listaVisitas']);
});

//Rutas de Carta Presentación
Route::prefix('cartaPres')->group(function (){
    Route::post('/register', [CartasPresentacionController::class, 'register']);
    Route::post('/update', [CartasPresentacionController::class, 'update']);
    Route::post('/delete', [CartasPresentacionController::class, 'delete']);
    Route::post('/verCartaPres', [CartasPresentacionController::class, 'verCartaPres']);
    Route::post('/listaCartasPres', [CartasPresentacionController::class, 'listaCartasPres']);
    Route::post('/firmaCartaPres', [CartasPresentacionController::class, 'firmaCartaPres']);
    Route::post('/descargarCartaPres', [CartasPresentacionController::class, 'descargarCartaPres']);
    Route::post('/contarCartasFirmar', [CartasPresentacionController::class, 'contarCartasFirmar']);
});

//Rutas de Seguimiento de Estadia
Route::prefix('segEstadia')->group(function (){
    Route::post('/register', [EstadiaSeguimientoController::class, 'register']);
    Route::post('/update', [EstadiaSeguimientoController::class, 'update']);
    Route::post('/delete', [EstadiaSeguimientoController::class, 'delete']);
    Route::post('/verSeguimiento', [EstadiaSeguimientoController::class, 'verSeguimiento']);
    Route::post('/listaSeguimientos', [EstadiaSeguimientoController::class, 'listaSeguimientos']);
});

//Rutas de Verificación de Documentos
Route::prefix('verifi')->group(function (){
    Route::post('/register', [VerificacionDocumentoController::class, 'register']);
    Route::post('/update', [VerificacionDocumentoController::class, 'update']);
    Route::post('/delete', [VerificacionDocumentoController::class, 'delete']);
    Route::post('/verVerificacion', [VerificacionDocumentoController::class, 'verVerificacion']);
    Route::post('/listaVerificaciones', [VerificacionDocumentoController::class, 'listaVerificaciones']);
    Route::post('/VerificacionesUsuario', [VerificacionDocumentoController::class, 'VerificacionesUsuario']);
});

Route::prefix('empresa')->group(function (){
    Route::post('/verEmpresa', [EmpresaController::class, 'verEmpresa']);
    Route::post('/listaEmpresas', [EmpresaController::class, 'listaEmpresas']);
    Route::post('/contarEmpresas', [EmpresaController::class, 'contarEmpresas']);
});

Route::prefix('cartaTer')->group(function (){
    Route::post('/register', [CartaTerminacionController::class, 'register']);
    Route::post('/update', [CartaTerminacionController::class, 'update']);
    Route::post('/delete', [CartaTerminacionController::class, 'delete']);
    Route::post('/verCartaTer', [CartaTerminacionController::class, 'verCartaTer']);
    Route::post('/listaCartasTer', [CartaTerminacionController::class, 'listaCartasTer']);
    Route::post('/descargarCartaTer', [CartaTerminacionController::class, 'descargarCartaTer']);
    Route::post('/contarCartasTer', [CartaTerminacionController::class, 'contarCartasTer']);
});