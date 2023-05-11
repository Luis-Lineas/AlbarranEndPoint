<?php

namespace App\Utils\Traits;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Trait de funciones comunes en las ordenes para usarse desde otros sitios
 */
trait CommonTrait
{
    /**
     * @throws Excepcion Si hay un error al consultar la base de datos.
     */
    private function createNewOrderInDB(int $idSucursal)
    {
        try {
            DB::beginTransaction();
            $maxOrden = DB::table('c_orden')
            ->selectRaw('MAX(orden)')
            ->where('orden', '<', 9000000)
            ->where('id_sucursal', $idSucursal)
            ->first();

            $aNumeracionActual = DB::table('a_numeracion')
            ->select('actual')
            ->where('vivo', true)
            ->where('id_numeracion_tipo', 1)
            ->where('id_sucursal', $idSucursal)
            ->first();


            if($maxOrden->max > $aNumeracionActual->actual) {
                $actual = $maxOrden->max;
            } else {
                $actual = $aNumeracionActual ? $aNumeracionActual->actual : 1;
            }

            $actual = $actual + 1;

            $newId = DB::table('c_orden')
            ->insertGetId([
                'orden' => $actual,
                'id_empresa' => Auth::user()->id_empresa,
                'id_sucursal' => $idSucursal,
                'id_pago_tipo' => 1,
                'id_pago' => 1,
                'fh_inicio' => Carbon::now(),
                'ck_imprimir_vehiculo' => true
            ]);

            $orden = DB::table('c_orden')
            ->where('id', $newId)
            ->first();

            DB::table('a_numeracion')
            ->where('vivo', true)
            ->where('id_numeracion_tipo', 1)
            ->where('id_sucursal', $idSucursal)
            ->update([
                'actual' => $actual
            ]);

            DB::commit();
            return $orden;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

