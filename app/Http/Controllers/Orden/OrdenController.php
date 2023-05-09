<?php

namespace App\Http\Controllers\Orden;

use App\Http\Controllers\Controller;
use App\Utils\HttpCodes;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrdenController extends Controller
{

    public function getAllOrdenes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idSucursal' => 'required|exists:a_sucursal,id',
            'fechaInicial' => 'required',
            'fechaFinal' => 'required',
            'estado'     => 'nullable|regex:/^[\d,]*$/',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), HttpCodes::HTTP_BAD_REQUEST);
        }
        $safe = $validator->validated();

        if($safe['idSucursal'] == 0){
            $safe['idSucursal'] = 'SELECT ID FROM a_sucursal WHERE vivo = true AND ck_almacen = false';
        }

        $ordenes = DB::table('c_orden')
            ->select('orden', 'id_sucursal', 'c_orden_estado.nombre AS estado_orden')
            ->join('c_orden_estado', 'c_orden_estado.id', '=', 'c_orden.id_orden_estado')
            ->whereRaw('id_sucursal IN ( '. $safe['idSucursal'] .' )')
            ->orderByDesc('fh_inicio');

        if ($request->filled('estado')) {
            $estados = explode(',', $safe['estado']);
            $ordenes = $ordenes->whereIn('id_orden_estado', $estados);
        }
        $ordenes = $ordenes
        ->paginate(200);

        if ($ordenes->isEmpty()) {
            return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
        }

        return response()->json($ordenes);
    }

    public function getOrden(int $idSucursal, int $orden)
    {
        $user = Auth::user();
        //TODO Validar que el usuario pueda observar u obtener la orden

        $ordenModel = DB::table('c_orden')
        ->where('id_sucursal', $idSucursal)
        ->where('orden', $orden)
        ->first();

        if($orden == null) {
            return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
        }

        $ordenModel->contenido = DB::table('c_orden_contenido')
        ->where('id_sucursal', $idSucursal)
        ->where('orden', $orden)
        ->get();

        $response = (object) [
            'orden' => $ordenModel
        ];
        return response()->json($response);
    }

    public function createNewOrden(Request $request)
    {
        //TODO Validar si el usuario puede crear ordenes en X sucursal
        $validator = Validator::make($request->all(), [
            'idSucursal' => 'required|exists:a_sucursal,id',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), HttpCodes::HTTP_BAD_REQUEST);
        }
        $safe = $validator->validated();

        try {
            dump($this->createNewOrderInDB($safe['idSucursal']));
            dd();
        } catch (Exception $e) {
            return response()->json($e->getMessage(), HttpCodes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

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
                $actual = !isset($aNumeracionActual->actual) ? $aNumeracionActual->actual : 1;
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
            return response()->json($orden, HttpCodes::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
