<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Basic\SucursalController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Orden\OrdenController;
use App\Http\Controllers\Orden\OrdenEstadoController;
use App\Http\Controllers\Productos\LlantasController;
use App\Http\Controllers\Vehiculo\BitacoraController;
use App\Http\Controllers\Vehiculo\InspeccionCortesiaController;
use App\Http\Controllers\Vehiculo\PlacaController;
use App\Http\Controllers\Web\VentasWebController;
use Illuminate\Support\Facades\Route;

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

//Authentication
Route::prefix('auth')->group(function($router) {
    Route::post('nomina/sign-in', [AuthController::class, 'loginWithNomina']);
    Route::post('sign-out', [AuthController::class, 'logout'])->middleware('jwt');
    Route::get('me', [AuthController::class, 'me'])->middleware('jwt');
});

//Placa
Route::prefix('placas')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        // Route::get('/{id}', [PlacaController::class, 'findById']);
        Route::get('/nombre', [PlacaController::class, 'findByPlaca']);
    });
});

//Inspeccion de Cortesia
Route::prefix('inspeccion-cortesia')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::get('/orden', [InspeccionCortesiaController::class, 'getOrdenByIdPlaca']);
        Route::get('/model', [InspeccionCortesiaController::class, 'getModelBD']);
        Route::post('/save', [InspeccionCortesiaController::class, 'save']);
        //Rutas con parametros opcionales
        Route::get('/{id?}', [InspeccionCortesiaController::class, 'getInspeccion']);
    });
});

//Bitacora
Route::prefix('bitacora')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::get('/placa', [BitacoraController::class, 'getBitacoraByIdPlaca']);
    });
});

//Clientes
Route::prefix('clientes')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::get('/placa', [ClienteController::class, 'findClienteByPlacaRelated']);
    });
});

//Productos
Route::prefix('productos')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::get('/llantas', [LlantasController::class, 'getLlantasToAlbarranWeb']);
    });
});

//Ordenes
Route::prefix('ordenes')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::get('/', [OrdenController::class, 'getAllOrdenes']);
        Route::get('/sucursal-{idSucursal}/orden-{orden}', [OrdenController::class, 'getOrden']);
        Route::post('/', [OrdenController::class, 'createNewOrden']);
    });
});

//Ordenes Estado
Route::prefix('orden-estado')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::get('/', [OrdenEstadoController::class, 'getAll']);
        //TODO Hacer Crud completo
        // Route::post('/', [OrdenController::class, 'createNewOrden']); //INFO se hara en futuras referencias
    });
});

//Ventas por medio de la web
Route::prefix('ventas-web')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::post('/venta', [VentasWebController::class, 'generarTranspasoAVentasWeb']);
    });
});

// //Sucursales
// Route::prefix('sucursal')->middleware('jwt')->group(function($route){
//     Route::get('/', [SucursalMicroserviceController::class, 'getAllSucursales']);
// });
