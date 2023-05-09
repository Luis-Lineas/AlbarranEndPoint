<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Utils\HttpCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    public function findClienteByPlacaRelated(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idPlaca' => 'required|exists:c_placa,id'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), HttpCodes::HTTP_BAD_REQUEST);
        }

        $safe = $validator->validated();

        $ordenAutoexpress = DB::table('c_orden_autoexpress')
        ->select('id_cliente')
        ->where('id_placa', $safe['idPlaca'])
        ->where('vivo', true)
        ->whereNotNull('fh_factura')
        ->orderByDesc('fh_factura')
        ->take(10);

        $orden = DB::table('c_orden')
        ->select('id_cliente')
        ->where('id_placa', $safe['idPlaca'])
        ->where('vivo', true)
        ->whereNotNull('fh_factura')
        ->orderByDesc('fh_factura')
        ->union($ordenAutoexpress)
        ->first();

        //no existe orden
        if($orden == null){
            return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
        }

        $cliente = DB::table('c_cliente')
        ->select('id', 'nombre', 'apellido_paterno', 'apellido_materno', 'razon_social', 'rfc', 'comentarios')
        ->where('id', $orden->id_cliente)
        ->where('vivo', true)
        ->first();

        //no existe orden
        if($cliente == null){
            return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
        }

        $response = (object) [
            'cliente' => $cliente
        ];

        return response()->json($response);
    }
}
