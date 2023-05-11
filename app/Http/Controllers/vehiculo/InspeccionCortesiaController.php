<?php

namespace App\Http\Controllers\Vehiculo;

use App\Http\Controllers\Controller;
use App\Http\Requests\InspeccionCortesia\OInspeccionRequest;
use App\Models\O_Inspeccion;
use App\Utils\HttpCodes;
use App\Utils\Traits\CommonTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InspeccionCortesiaController extends Controller
{
    use CommonTrait;

    public function getOrdenByIdPlaca(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idSucursal' => 'required|exists:a_sucursal,id',
            'idPlaca' => 'required|exists:c_placa,id'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), HttpCodes::HTTP_BAD_REQUEST);
        }

        $safe = $validator->validated();

        $orden = DB::table('c_orden')
        ->where('vivo', true)
        ->where(function($query) use ($safe) {
            $query->orWhere('id_placa', $safe['idPlaca'])
            ->orWhereNull('id_placa');
        })
        ->where('id_sucursal', $safe['idSucursal'])
        ->whereNotIn('id_orden_estado', [2, 6])
        ->orderByDesc('fh_inicio')
        ->first();

        $statusCode = HttpCodes::HTTP_I_AM_A_TEAPOT;

        if($orden == null) { //Crea una nueva orden
            try {
                $orden = $this->createNewOrderInDB($safe['idSucursal']);

                $statusCode = HttpCodes::HTTP_CREATED;
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json($e->getMessage(), HttpCodes::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $statusCode = HttpCodes::HTTP_OK;
        }

        try { //Settea el id de la placa y el cliente una vez obtenida los datos
            $idCliente = DB::table('c_orden')
            ->select('id_cliente')
            ->where('id_placa', $safe['idPlaca'])
            ->where('vivo', true)
            ->whereNotNull('fh_factura')
            ->orderByDesc('fh_factura')
            ->first();

            $orden->id_placa = $safe['idPlaca'];
            $orden->id_cliente = $idCliente ? $idCliente->id_cliente : null;

            DB::beginTransaction();
            DB::table('c_orden')
            ->where('id', $orden->id)
            ->update([
                'id_placa' => $orden->id_placa,
                'id_cliente' => $orden->id_cliente
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), HttpCodes::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = (object) ['orden' => $orden];
        return response()->json($response, $statusCode);
    }

    public function save(OInspeccionRequest $request)
    {
        $safe = $request->validated();
        $safe['nomina'] = Auth::user()->nomina;
        $oInspeccionCortesia = new O_Inspeccion($safe);
        // dump($oInspeccionCortesia);
        try {
            DB::beginTransaction();
            $oInspeccionCortesia->save();
            DB::commit();
            return response()->json($oInspeccionCortesia, HttpCodes::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), HttpCodes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getModelBD()
    {
        $inspeccionCortesia = new O_Inspeccion();

        $response = (object) [
            'inspeccionCortesia' => $inspeccionCortesia->getCasts()
        ];
        return response()->json($response);
    }
}
