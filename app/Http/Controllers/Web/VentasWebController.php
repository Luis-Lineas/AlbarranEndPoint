<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Utils\HttpCodes;
use App\Utils\Traits\AlmacenTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VentasWebController extends Controller
{

    use AlmacenTrait;

    public function generarTranspasoAVentasWeb(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'venta' => 'required|array|min:1',
            'venta.*.idProducto' => 'required|exists:c_producto,id',
            'venta.*.cantidad' => 'required|integer|min:1',
        ]);

        if($validator->fails()){
            return response()->json(['errors' =>
                $validator->errors()], HttpCodes::HTTP_BAD_REQUEST);
        }

        $safe = $validator->validate();
        $errors = array();
        $transpasoContenido = array();

        foreach($safe['venta'] as $producto) {
            if($this->validateSiExisteStock($producto['idProducto'], $producto['cantidad']) == false) //No hay stock
            {
                $error = (object) [
                    'idProducto' => $producto['idProducto'],
                    'message' => 'Actualmente no hay stock suficiente para cubrir la venta'
                ];
                array_push($errors, $error);
                continue;
            }
            $stock = $this->getSucursalConMayorStock($producto['idProducto']);

            $producto['stock'] = $stock;
            array_push($transpasoContenido, $producto);
        }

        if(empty($errors) == false) //Hay errores de validacion en la peticion
        {
            return response()->json(['errors' => $errors], HttpCodes::HTTP_CONFLICT);
        }

        //Guardar transpaso en la base de datos
        $transpasos = array();
        foreach($transpasoContenido as $producto) //Todas las variables dentro de este segmento son arrays
        {
            foreach ($producto['stock'] as $stock) //Todas las sucursales que tienen stock para ese producto
            {
                if($producto['cantidad'] == 0) //Ya no hay productos que generar el transpaso, pasa al siguiente producto
                    break;
                $transpaso = $this->saveTranspasoEnBD(
                    [(object)
                    [
                        'idProducto' => $producto['idProducto'],
                        'cantidad' => $producto['cantidad']]
                    ], //contenido
                    98, //sucursal recibe
                    $stock->id_sucursal, //sucursal envia
                    98); //sucursal solicita
                $producto['cantidad'] = $producto['cantidad'] - $stock->existencia_final;
                array_push($transpasos, $transpaso);

                if($producto['cantidad'] <= $stock->existencia_final)
                {
                    //Genera el transpaso completo y pasa al siguiente producto
                    break;
                } else {
                    //Se genera el transpaso con la sucursal, y se le descuenta a la cantidad de productos requeridos para pasar a la siguiente sucursal
                    continue;
                }
            }
        }

        return response()->json($transpasos, HttpCodes::HTTP_CREATED);
    }

    private function getSucursalConMayorStock(int $idProducto) : Collection
    {
        $productosEnOrdenes = DB::table('c_orden_contenido')
        ->select('c_orden.id_sucursal')
        ->selectRaw('CASE WHEN SUM(c_orden_contenido.cantidad) IS NULL THEN 0 ELSE SUM(c_orden_contenido.cantidad) END AS sum')
        ->join('c_orden', function($query) {
            $query->on('c_orden.orden', '=', 'c_orden_contenido.orden');
            $query->on('c_orden.id_sucursal', '=', 'c_orden_contenido.id_sucursal');
        })
        ->where('c_orden_contenido.id_producto', $idProducto)
        ->whereNotIn('c_orden.id_orden_estado', [5,6])
        ->where('c_orden_contenido.vivo', true)
        ->groupBy('c_orden.id_sucursal')
        ->orderBy('sum');

        $productosEnStock = DB::table('f_almacen')
        ->select('f_almacen.id_sucursal')
        ->selectRaw('CASE WHEN t_ordenes.sum IS NULL THEN existencia ELSE existencia - t_ordenes.sum END AS existencia_final')
        ->where('id_producto', $idProducto)
        ->where('vivo', true)
        ->where('existencia', '>', 0)
        ->leftJoinSub($productosEnOrdenes, 't_ordenes', 't_ordenes.id_sucursal', '=', 'f_almacen.id_sucursal')
        ->orderBy('existencia_final', 'DESC')
        ->get();

        return collect($productosEnStock);
    }




}
