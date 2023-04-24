<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Productos\LlantasController;
use App\Http\Controllers\Vehiculo\BitacoraController;
use App\Http\Controllers\Vehiculo\InspeccionCortesiaController;
use App\Http\Controllers\Vehiculo\PlacaController;
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

Route::prefix('placas')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        // Route::get('/{id}', [PlacaController::class, 'findById']);
        Route::get('/nombre', [PlacaController::class, 'findByPlaca']);
    });
});

Route::prefix('inspeccion-cortesia')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::get('/model', [InspeccionCortesiaController::class, 'getModelBD']);
        Route::post('/save', [InspeccionCortesiaController::class, 'getModelBD']);
    });
});


Route::prefix('bitacora')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::get('/placa', [BitacoraController::class, 'getBitacoraByIdPlaca']);
    });
});

Route::prefix('clientes')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::get('/placa', [ClienteController::class, 'findClienteByPlacaRelated']);
    });
});

Route::prefix('productos')->middleware('jwt')->group(function($router) {
    Route::prefix('v1')->group(function($router){
        Route::get('/llantas', [LlantasController::class, 'getLlantas']);
    });
});

// //Sucursales
// Route::prefix('sucursal')->middleware('jwt')->group(function($route){
//     Route::get('/', [SucursalMicroserviceController::class, 'getAllSucursales']);
// });
