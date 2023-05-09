<?php

namespace App\Http\Controllers\Vehiculo;

use App\Http\Controllers\Controller;
use App\Utils\HttpCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BitacoraController extends Controller
{
    public function getBitacoraByIdPlaca(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idPlaca' => 'required|exists:c_placa,id'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), HttpCodes::HTTP_BAD_REQUEST);
        }

        $safe = $validator->validated();

        $bitacoraAutoexpress = $this->getBitacoraFromAutoexpress($safe['idPlaca']);
        $bitacoraOrgAlbarran = $this->getBitacoraFromOrgAlbarran($safe['idPlaca']);

        $bitacora = $bitacoraOrgAlbarran->merge($bitacoraAutoexpress);

        if($bitacora == null){
            return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
        }

        return response()->json($bitacora);
    }

    //Private functions
    private function getBitacoraFromAutoexpress(int $idPlaca) : Collection
    {
        $bitacora = DB::select(DB::raw("
        SELECT
            t1.*
            FROM
            dblink ( 'host=albarran.cfddpggllvms.us-east-1.rds.amazonaws.com user=dbmasteralbarran password=LlGZf9ozYXOyBUZt dbname=Albarran port=1464' :: TEXT,
            'SELECT * FROM v_bitacora WHERE id_placa = " .$idPlaca ." ORDER BY fecha DESC;' :: TEXT, FALSE ) t1
            (
                id_sucursal INTEGER,
                id_placa INTEGER,
                fecha DATE,
                factura VARCHAR,
                kilometraje INTEGER,
                llantas  VARCHAR,
                montaje  VARCHAR,
                balanceo  VARCHAR,
                alineacion  VARCHAR,
                nitrogeno  VARCHAR,
                cambio_aceite  VARCHAR,
                amortiguadores  VARCHAR,
                frenos  VARCHAR,
                suspension  VARCHAR,
                afinacion  VARCHAR,
                verificacion  VARCHAR,
                filtro_aire  VARCHAR,
                filtro_cabina  VARCHAR,
                mecanica_express  VARCHAR,
                autoelectrico  VARCHAR,
                acumuladores  VARCHAR,
                refrigerante  VARCHAR,
                ac  VARCHAR,
                aceite_transmision VARCHAR,
                otros  VARCHAR,
                total  VARCHAR,
                observaciones  VARCHAR
            )
        "));
        return collect($bitacora);
    }

    private function getBitacoraFromOrgAlbarran(int $idPlaca) : Collection
    {
        $bitacora = DB::table('v_bitacora')
        ->where('id_placa', $idPlaca)
        ->orderByDesc('fecha')
        ->get();

        return $bitacora;
    }


}
