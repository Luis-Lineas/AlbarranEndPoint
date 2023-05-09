<?php

namespace App\Http\Controllers\Orden;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenEstadoController extends Controller
{
    public function getAll()
    {
        $estados = DB::table('c_orden_estado')
        ->where('vivo', true)
        ->orderBy('id')
        ->get();

        return response()->json($estados);
    }
}
