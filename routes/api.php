<?php

use App\Http\Controllers\Auth\AuthController;
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
// //Sucursales
// Route::prefix('sucursal')->middleware('jwt')->group(function($route){
//     Route::get('/', [SucursalMicroserviceController::class, 'getAllSucursales']);
// });
// //Codigos autorizacion
// Route::prefix('codigos-autorizacion')->middleware('jwt')->group(function($router) {
//     Route::get('codigo', [CodigosAutorizacionController::class, 'getCodigo']);
//     Route::post('unlock', [CodigosAutorizacionController::class, 'unlockCodigo']);
//     Route::get('compra/validate', [CodigosAutorizacionController::class, 'validateCompra']);
//     Route::get('cuenta/validate', [CodigosAutorizacionController::class, 'validateCuentaCredito']);
//     Route::get('tipos', [CodigosAutorizacionController::class, 'getTiposCodigo']);
//     Route::get('cuenta/detalle', [CodigosAutorizacionController::class, 'getCuentaCreditoDetalles']);
//     Route::put('cuenta/vigencia', [CodigosAutorizacionController::class, 'editVigenciaCuenta']);
// });
