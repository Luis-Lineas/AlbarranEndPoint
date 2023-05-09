<?php

namespace App\Http\Controllers\Vehiculo;

use App\Http\Controllers\Controller;
use App\Http\Requests\InspeccionCortesia\OInspeccionRequest;
use App\Models\O_Inspeccion;
use App\Utils\HttpCodes;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InspeccionCortesiaController extends Controller
{

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
