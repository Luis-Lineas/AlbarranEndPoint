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
            $contenido = collect($contenido);

            $indice = 0;

            $traspaso = (object) [
                'id' => null,
                'traspaso' => '',
                'contenido' => collect()
            ];

            $numero = DB::table('a_numeracion')
            ->selectRaw('CASE WHEN actual IS NULL THEN 1 ELSE actual END AS numeracion')
            ->where('id_sucursal', $idSucursalSolicita)
            ->where('id_numeracion_tipo', 7)
            ->where('vivo', true)
            ->first();

            $numero->numeracion = $numero->numeracion + 1;

            //Solicita + Envia + Recibe + Numeracion actual
            $traspaso->traspaso = ''
            . $idSucursalSolicita
            . $idSucursalEnvia
            . $idSucursalRecibe
            . $numero->numeracion;

            foreach ($contenido as $producto) {
                $indice = $indice + 1;
                $costo = DB::table('c_precio')
                ->select('precio')
                ->where('id_producto', $producto->idProducto)
                ->where('vivo', true)
                ->first();

                $traspasoContenido = [
                    'vivo' => true,
                    'traspaso' => $traspaso->traspaso,
                    'numero' => $indice,
                    'id_producto' => $producto->idProducto,
                    'cantidad' => $producto->cantidad,
                    'costo_unitario' => $costo->precio,
                    'costo_total' => floatval($costo->precio) * $producto->cantidad
                ];

                $id = DB::table('f_traspaso_contenido')->insertGetId($traspasoContenido);
                $traspasoContenido['id'] = $id;

                $traspaso->contenido->push((object) $traspasoContenido);

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
                    'fh_movimiento' => Carbon::now(),
                    'referencia' => $traspaso->traspaso,
                    'id_almacen_kardex_proveedor' => 2, //id_kardex_proveedor // id_almacen_nombre
                    'iproveedor' => $idSucursalEnvia
                ]);
            }

            $traspaso->id = DB::table('f_traspaso')->insertGetId([
                'id_sucursal_envia' => $idSucursalEnvia,
                'id_sucursal_recive' => $idSucursalRecibe,
                'id_usuario_envia' => Auth::user()->id,
                'id_usuario_recive' => Auth::user()->id,
                'fh_envio' => Carbon::now(),
                'cantidad_envio' => $contenido->sum('cantidad'),
                'traspaso' => $traspaso->traspaso,
                'total' => $traspaso->contenido->sum('costo_total'),
                'ck_solicitado' => false,
                'ck_enviado' => true,
                'id_transporte' => 1,
                'id_transporte_chofer' => 10001, //id_operador = Albarran
                'id_sucursal' => $idSucursalSolicita,
                'costo' => 0,
                'cantidad_servicio' => 1,
                'comision' => 0,
            ]);

            DB::table('a_numeracion')
            ->where('id_numeracion_tipo', 7)
            ->where('id_sucursal', $idSucursalSolicita)
            ->where('vivo', true)
            ->update([
                'actual' => $numero->numeracion
            ]);


            // dump($traspaso);
            DB::commit();
            return $traspaso;
        } catch (Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
