<?php

namespace App\Http\Controllers\Vehiculo;

use App\Http\Controllers\Controller;
use App\Utils\HttpCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlacaController extends Controller
{
    public function findByPlaca(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'placa' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), HttpCodes::HTTP_BAD_REQUEST);
        }

        $safe = $validator->validated();

        $placa = DB::table('c_placa')
        //Placa
        ->select('c_placa.id', 'c_placa.nombre AS placa', 'modelo', 'fecha_alta', 'kilometraje', 'visitas', 'comentarios',
        //marca vehiculo
        'c_marca_vehiculo.id AS id_marca_vehiculo',
        'c_marca_vehiculo.nombre AS marca_vehiculo',
        //modelo vehiculo
        'c_modelo_vehiculo.id AS id_modelo_vehiculo',
        'c_modelo_vehiculo.nombre AS modelo_vehiculo',
        //llanta vehiculo
        'c_medida_llanta.id AS id_llanta_vehiculo',
        'c_medida_llanta.nombre AS llanta_vehiculo')
        ->leftJoin('c_marca_vehiculo', 'c_marca_vehiculo.id', '=', 'c_placa.id_marca_vehiculo')
        ->leftJoin('c_modelo_vehiculo', 'c_modelo_vehiculo.id', '=', 'c_placa.id_modelo_vehiculo')
        ->leftJoin('c_medida_llanta', 'c_medida_llanta.id', '=', 'c_placa.id_medida_llanta')
        ->where('c_placa.nombre', 'ILIKE', $safe['placa'])
        ->where('c_placa.vivo', true)
        ->first();

        if($placa == null){
            return response()->json(['exist' => false], HttpCodes::HTTP_NO_CONTENT);
        }

        $response = (object) [
            'placa' => $placa
        ];

        return response()->json($response);
    }
}
