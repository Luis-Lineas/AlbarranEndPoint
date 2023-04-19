<?php

namespace App\Http\Controllers\basic;

use App\Http\Controllers\Controller;
use App\Utils\HttpCodes;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Facades\DB;

class SucursalController extends Controller
{
    public function getAllSucursales()
    {
        $sucursales = DB::table('a_sucursal')
        ->select('id', 'nombre')
        ->where('vivo', true)
        ->where('ck_almacen', false)
        ->orderBy('nombre')->get();

        if($sucursales == null) {
            return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
        }

        return response()->json($sucursales);
    }
}
