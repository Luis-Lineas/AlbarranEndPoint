<?php

namespace App\Http\Controllers\Productos;

use App\Http\Controllers\Controller;
use App\Utils\HttpCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LlantasController extends Controller
{
    public function getLlantas(Request $request)
    {

        $existencias = DB::table('f_almacen')
        ->select('id_producto', DB::raw('SUM(existencia) AS existencia'))
        ->where('existencia', '>', 0)
        ->where('vivo', true)
        ->groupBy('id_producto')
        ->orderBy('id_producto');

        $llantas = DB::table('c_producto')
        ->select('c_producto.id','c_producto.codigo_anterior', 'c_producto.nombre AS producto', 'c_precio.precio', 'c_precio.precio_internet_iva', 'existencia')
        ->leftJoin('c_precio', 'c_producto.id', '=', 'c_precio.id_producto')
        ->joinSub($existencias, 't_existencias', 't_existencias.id_producto', '=', 'c_producto.id')
        ->whereIn('id_linea', [5, 8])
        ->where('c_producto.vivo', true)
        ->orderBy('c_producto.nombre')
        ->get();

        if($llantas == null){
            return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
        }

        return response()->json($llantas);
    }
}
