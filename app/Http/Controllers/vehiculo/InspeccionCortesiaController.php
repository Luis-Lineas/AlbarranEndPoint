<?php

namespace App\Http\Controllers\Vehiculo;

use App\Http\Controllers\Controller;
use App\Models\O_Inspeccion;
use Illuminate\Http\Request;

class InspeccionCortesiaController extends Controller
{
    public function getModelBD()
    {
        $inspeccionCortesia = new O_Inspeccion();

        $response = (object) [
            'inspeccionCortesia' => $inspeccionCortesia->getCasts()
        ];
        return response()->json($response);
    }
}
