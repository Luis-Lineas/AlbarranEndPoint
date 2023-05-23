<?php

namespace App\Utils\Traits;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Trait que manejara todas las funcionalidades con Almacen
 */
trait AlmacenTrait
{
    /**
     * @param int $idProducto Producto que va a validarse si tiene stock
     * @param int $cantidadAUsarse Cantidad que va a usarse
     * @param int $idSucursal Sucursal que va a ser usado, default null si se va a comparar con todas las sucursales
     */
    public function validateSiExisteStock(int $idProducto, int $cantidadAUsarse, int $idSucursal = null) : bool
    {
        $productosEnOrdenes = DB::table('c_orden_contenido')
        ->selectRaw('CASE WHEN SUM(c_orden_contenido.cantidad) IS NULL THEN 0 ELSE SUM(c_orden_contenido.cantidad) END AS sum')
        ->join('c_orden', function($query) {
            $query->on('c_orden.orden', '=', 'c_orden_contenido.orden');
            $query->on('c_orden.id_sucursal', '=', 'c_orden_contenido.id_sucursal');
        })
        ->where('c_orden_contenido.id_producto', $idProducto)
        ->whereNotIn('c_orden.id_orden_estado', [5,6])
        ->where('c_orden_contenido.vivo', true);


        $productoEnAlmacen = DB::table('f_almacen')
        ->selectRaw('SUM(existencia)')
        ->where('id_producto', $idProducto)
        ->where('vivo', true);

        if(isset($idSucursal)){
            $productosEnOrdenes->where('c_orden.id_sucursal', $idSucursal);
            $productoEnAlmacen->where('id_sucursal', $idSucursal);
        }
        $productosEnOrdenes = $productosEnOrdenes->first();
        $productoEnAlmacen = $productoEnAlmacen->first();

        $productosDisponibles = $productoEnAlmacen->sum - $productosEnOrdenes->sum;

        return $productosDisponibles >= $cantidadAUsarse;
    }

    /**
     * Guarda el transpaso en la Base de datos
     * Los campos de contenido debe ser:
     *  - idProducto : ID del producto que se va a transpasar, proveniente de c_producto
     *  - cantidad   : Cantidad de productos que se busca transpasar
     *
     * @param array $contenido Arreglo de productos que se enviaran en el transpaso
     * @param int $idSucursalRecibe Sucursal que recibe el transpaso
     * @param int $idSucursalEnvia Sucursal que envia el transpaso
     * @throws Exception No se puede guardar el transpaso
     * @return object Transpaso guardado
     */
    private function saveTranspasoEnBD(array $contenido, int $idSucursalRecibe, int $idSucursalEnvia, int $idSucursalSolicita = null)
    {
        try {
            DB::beginTransaction();
            $idSucursalSolicita = ($idSucursalSolicita == null ? Auth::user()->id_sucursal : $idSucursalSolicita);

            $indice = 0;

            dd(Carbon::now());

            $traspaso = (object) [
                'traspaso' => '',
                'contenido' => array()
            ];

            $numero = DB::table('a_numeracion')
            ->selectRaw('CASE WHEN actual IS NULL THEN 1 ELSE actual + 1 END AS numeracion')
            ->where('id_sucursal', $idSucursalEnvia)
            ->where('vivo', true)
            ->first();

            //Solicita + Envia + Recibe + Numeracion actual
            $traspaso->traspaso = ''
            . $idSucursalSolicita
            . $idSucursalEnvia
            . $idSucursalRecibe
            . $numero->numeracion;

            foreach ($contenido as $producto) {
                $costo = DB::table('c_precio')
                ->select('precio')
                ->where('id_producto', $producto->idProducto)
                ->where('vivo', true)
                ->first();

                $id = DB::table('f_traspaso_contenido')
                ->insertGetId([
                    'vivo' => true,
                    'traspaso' => $traspaso->traspaso,
                    'numero' => $indice++,
                    'id_producto' => $producto->idProducto,
                    'cantidad' => $producto->cantidad,
                    'costo_unitario' => $costo->precio,
                    'costo_total' => floatval($costo->precio) * $producto->cantidad
                ]);
                array_push($traspaso->contenido, $id);

                $kardex = DB::table('f_almacen_kardex')
                ->select('id', 'numero_movimiento', 'existencia_final')
                ->where('id_producto', $producto->idProducto)
                ->where('id_sucursal', $idSucursalEnvia)
                ->where('vivo', true)
                ->orderBy('numero_movimiento', 'desc')
                ->first();

                $kardex->existencia_final = $kardex->existencia_final - $producto->cantidad;

                DB::table('f_almacen_kardex')
                ->insert([
                    'id_producto' => $producto->idProducto,
                    'id_sucursal' => $idSucursalEnvia,
                    'id_usuario' => Auth::user()->id,
                    'numero_movimiento' => $kardex->numero_movimiento,
                    'existencia_final' => $kardex->existencia_final,
                    'id_almacen_movimiento' => 8, //"Traspaso Salida"
                    'fh_algo' => Carbon::now()
                ]);
            }
            dump($traspaso);
            DB::rollBack();
            return $traspaso;
        } catch (Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
