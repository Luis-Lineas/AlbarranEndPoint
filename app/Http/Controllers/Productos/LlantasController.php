<?php

namespace App\Http\Controllers\Productos;

use App\Http\Controllers\Controller;
use App\Utils\HttpCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LlantasController extends Controller
{
    public function getLlantasToAlbarranWeb(Request $request)
    {

        $productosEnOrdenes = DB::table('c_orden_contenido')
        ->select('id_producto')
        ->selectRaw('CASE WHEN SUM(c_orden_contenido.cantidad) IS NULL THEN 0 ELSE SUM(c_orden_contenido.cantidad) END AS usadoordenes')
        ->join('c_orden', function($query) {
            $query->on('c_orden.orden', '=', 'c_orden_contenido.orden');
            $query->on('c_orden.id_sucursal', '=', 'c_orden_contenido.id_sucursal');
        })
        ->whereNotIn('c_orden.id_orden_estado', [5,6])
        ->where('c_orden_contenido.vivo', true)
        ->groupBy('id_producto');

        $existencias = DB::table('f_almacen')
        ->select('id_producto')
        ->selectRaw('SUM(existencia) AS existencia')
        ->where('existencia', '>', 0)
        ->where('vivo', true)
        ->groupBy('id_producto')
        ->orderBy('id_producto');

        $llantas = DB::table('c_producto')
        ->select('c_producto.id', 'c_producto.codigo_anterior', 'c_producto.nombre AS producto', 'c_precio.precio', 'c_precio.precio_internet_iva')
        ->selectRaw('CASE WHEN existencia > 4 THEN 4 ELSE existencia END AS existencia')
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
