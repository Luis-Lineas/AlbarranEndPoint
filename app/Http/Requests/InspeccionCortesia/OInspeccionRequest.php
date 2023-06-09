<?php

namespace App\Http\Requests\InspeccionCortesia;

use App\Utils\HttpCodes;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class OInspeccionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return Auth::id() != null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'id' => 'nullable|numeric',
            'id_sucursal' => 'required|numeric|exists:a_sucursal,id',
            'orden' => 'required|numeric|exists:c_orden,orden',
            'nomina' => 'nullable|numeric',
            'nuevo' => 'nullable|boolean',
            'vivo' => 'nullable|boolean',
            'modificado' => 'nullable|boolean',
            'reply' => 'nullable|boolean',
            'id_placa' => 'required|numeric|exists:c_placa,id',
            'id_cliente' => 'required|numeric|exists:c_cliente,id',
            'afinacion' => 'required|in:0,1,2',
            'filtro_aire' => 'required|in:0,1,2',
            'filtro_gas' => 'required|in:0,1,2',
            'bujias' => 'required|in:0,1,2',
            'pvc' => 'required|in:0,1,2',
            'humo' => 'required|in:0,1,2',
            'gases' => 'required|in:0,1,2',
            'tapon_aceite' => 'required|in:0,1,2',
            'bayoneta' => 'required|in:0,1,2',
            'reseteo' => 'required|boolean',
            'sus_eje' => 'required|boolean',
            'sus_rotu' => 'required|boolean',
            'sus_hules' => 'required|boolean',
            'sus_bujes' => 'required|boolean',
            'sus_horqui' => 'required|boolean',
            'sus_mangos' => 'required|boolean',
            'sus_pitman' => 'required|boolean',
            'sus_aux' => 'required|boolean',
            'sus_homo' => 'required|boolean',
            'sus_resortes' => 'required|boolean',
            'dire_bomba' => 'required|boolean',
            'dire_polvo' => 'required|boolean',
            'dire_crema' => 'required|boolean',
            'dire_varillas' => 'required|boolean',
            'dire_termina' => 'required|boolean',
            'amor_fuga' => 'required|boolean', 
            'amor_golpe' => 'required|boolean',
            'amor_spresion' => 'required|boolean',
            'amor_reem' => 'required|boolean',
            'daceite_bajo' => 'required|boolean',
            'daceite_contami' => 'required|boolean',
            'escape_roto' => 'required|boolean',
            'escape_fugas' => 'required|boolean',
            'cata_roto' => 'required|boolean',
            'cata_degra' => 'required|boolean',
            'cata_reem' => 'required|boolean',
            'fugas_refri' => 'required|boolean',
            'fugas_aceite' => 'required|boolean',
            'fugas_frenos' => 'required|boolean',
            'fugas_dire' => 'required|boolean',
            'fugas_tran' => 'required|boolean',
            'suspension' => 'required|in:0,1,2',
            'direccion' => 'required|in:0,1,2',
            'amortigua' => 'required|in:0,1,2',
            'aceite_dif' => 'required|in:0,1,2',
            'sis_escape' => 'required|in:0,1,2',
            'catalitico' => 'required|in:0,1,2',
            'fugas' => 'required|in:0,1,2',
            'aceite_bajo' => 'required|boolean',
            'aceite_degradado' => 'required|boolean',
            'refri_bajo' => 'required|boolean',
            'refri_degradado' => 'required|boolean',
            'sumang_reseca' => 'required|boolean',
            'sumang_agrie' => 'required|boolean',
            'sumang_rota' => 'required|boolean',
            'inmang_reseca' => 'required|boolean',
            'inmang_agrie' => 'required|boolean',
            'inmang_rota' => 'required|boolean',
            'banda_reseca' => 'required|boolean',
            'banda_agrie' => 'required|boolean',
            'banda_rota' => 'required|boolean',
            'bate_bajo' => 'required|boolean',
            'bate_corto' => 'required|boolean',
            'bate_rota' => 'required|boolean',
            'alterna_ruido' => 'required|boolean',
            'alterna_especi' => 'required|boolean',
            'atrans_bajo' => 'required|boolean',
            'atrans_degra' => 'required|boolean',
            'adire_bajo' => 'required|boolean',
            'adire_degra' => 'required|boolean',
            'liq_bajo' => 'required|boolean',
            'liq_degra' => 'required|boolean',
            'somotor_roto' => 'required|boolean',
            'somotor_suelto' => 'required|boolean',
            'sotrans_roto' => 'required|boolean',
            'sotrans_suelto' => 'required|boolean',
            'reseteo_cofre' => 'required|boolean',
            'aceite' => 'required|in:0,1,2',
            'refrigerante' => 'required|in:0,1,2',
            'manguera_su' => 'required|in:0,1,2',
            'manguera_inf' => 'required|in:0,1,2',
            'banda' => 'required|in:0,1,2',
            'bateria' => 'required|in:0,1,2',
            'alternador' => 'required|in:0,1,2',
            'transmision' => 'required|in:0,1,2',
            'limpiapara' => 'required|in:0,1,2',
            'so_motor' => 'required|in:0,1,2',
            'so_transmision' => 'required|in:0,1,2',
            'direccion_cofre' => 'required|in:0,1,2',
            'luz_dd_alta' => 'required|boolean',
            'luz_dd_baja' => 'required|boolean',
            'luz_dd_cuarto' => 'required|boolean',
            'luz_dd_dire' => 'required|boolean',
            'luz_di_alta' => 'required|boolean',
            'luz_di_baja' => 'required|boolean',
            'luz_di_cuarto' => 'required|boolean',
            'luz_di_dire' => 'required|boolean',
            'luz_td_cuarto' => 'required|boolean',
            'luz_td_stop' => 'required|boolean',
            'luz_td_rever' => 'required|boolean',
            'luz_td_dire' => 'required|boolean',
            'luz_ti_cuarto' => 'required|boolean',
            'luz_ti_stop' => 'required|boolean',
            'luz_ti_rever' => 'required|boolean',
            'luz_ti_dire' => 'required|boolean',
            'luz_emer' => 'required|boolean',
            'limpia_liq' => 'required|boolean',
            'limpia_hules' => 'required|boolean',
            'limpia_rocia' => 'required|boolean',
            'ot_espe' => 'required|boolean',
            'ot_ante' => 'required|boolean',
            'ot_cris' => 'required|boolean',
            'ot_moldu' => 'required|boolean',
            'ot_carro' => 'required|boolean',
            'ot_micas' => 'required|boolean',
            'luces_dd' => 'required|in:0,1,2',
            'luces_di' => 'required|in:0,1,2',
            'luces_td' => 'required|in:0,1,2',
            'luces_ti' => 'required|in:0,1,2',
            'luces_eme' => 'required|in:0,1,2',
            'limpiabrisas' => 'required|in:0,1,2',
            'otros' => 'required|in:0,1,2',
            'bomba' => 'required',
            'balatas' => 'required|boolean',
            'disco' => 'required|boolean',
            'tambores' => 'required|boolean',
            'pistones' => 'required|boolean',
            'cilindros' => 'required|boolean',
            'booster' => 'required|boolean',
            'liquido' => 'required|boolean',
            'calipers' => 'required|boolean',
            'tubos' => 'required|boolean',
            'mangueras' => 'required|boolean',
            'frenomano' => 'required|boolean',
            'ajuste' => 'required|boolean',
            'limpieza' => 'required|boolean',
            'frenos' => 'required|in:0,1,2',
            'luces_tablero' => 'required|boolean',
            'luces_cortesia' => 'required|boolean',
            'luces_puerta' => 'required|boolean',
            'airea_noenfria' => 'required|boolean',
            'airea_conflujo' => 'required|boolean',
            'aire_mante' => 'required|boolean',
            'cabina_sucio' => 'required|boolean',
            'cabina_reemplaza' => 'required|boolean',
            'claxon_nofun' => 'required|boolean',
            'claxon_anormal' => 'required|boolean',
            'equipo_espejo' => 'required|boolean',
            'equipo_viseras' => 'required|boolean',
            'equipo_radio' => 'required|boolean',
            'equipo_seguros' => 'required|boolean',
            'equipo_eleva' => 'required|boolean',
            'equipo_cinturon' => 'required|boolean',
            'ck_iconos' => 'required|boolean',
            'iconos' => 'required|in:0,1,2',
            'luces' => 'required|in:0,1,2',
            'aire' => 'required|in:0,1,2',
            'cabina' => 'required|in:0,1,2',
            'claxon' => 'required|in:0,1,2',
            'equipo' => 'required|in:0,1,2',
            'txt_iconos' => 'nullable|string',
            'txt_otros' => 'nullable|string',
            'alineacion' => 'required|in:0,1,2',
            'balanceo' => 'required|in:0,1,2',
            'rotacion' => 'required|in:0,1,2',
            'reemplazo' => 'required|in:0,1,2',
            'desgaste_dd' => 'required|in:0,1,2',
            'desgaste_di' => 'required|in:0,1,2',
            'desgaste_td' => 'required|in:0,1,2',
            'desgaste_ti' => 'required|in:0,1,2',
            'desgaste_ref' => 'required|numeric',
            'presion_di_a' => 'required|numeric',
            'presion_di_d' => 'required|numeric',
            'presion_dd_a' => 'required|numeric',
            'presion_dd_d' => 'required|numeric',
            'presion_ti_a' => 'required|numeric',
            'presion_ti_d' => 'required|numeric',
            'presion_td_a' => 'required|numeric',
            'presion_td_d' => 'required|numeric',
            'presion_ref_a' => 'required|numeric',
            'presion_ref_d' => 'required|numeric',
            'gato' => 'required|boolean',
            'torque' => 'required|boolean',
            'otros_llantas' => 'nullable|boolean',
            'fh_creacion' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'fecha_llanta' => 'required|date',
            'fecha_freno' => 'required|date',
            'fecha_afinacion' => 'required|date',
            'fecha_interior' => 'required|date',
            'fecha_bajo' => 'required|date',
            'fecha_cofre' => 'required|date',
            'fecha_exterior' => 'required|date',
            'txt_ofrece' => 'nullable|string',
            'txt_solicita' => 'nullable|string',
            'cotizacion_interior' => 'nullable|string',
            'cotizacion_auto' => 'nullable|string',
            'cotizacion_exterior' => 'nullable|string',
            'cotizacion_cofre' => 'nullable|string',
            'cotizacion_llantas' => 'nullable|string',
            'cotizacion_afinacion' => 'nullable|string',
            'cotizacion_frenos' => 'nullable|string',
            'golpes_di' => 'required|boolean',
            'clavos_di' => 'required|boolean',
            'bolas_di' => 'required|boolean',
            'corta_di' => 'required|boolean',
            'desgas_di' => 'required|boolean',
            'golpes_dd' => 'required|boolean',
            'clavos_dd' => 'required|boolean',
            'bolas_dd' => 'required|boolean',
            'corta_dd' => 'required|boolean',
            'desgas_dd' => 'required|boolean',
            'golpes_td' => 'required|boolean',
            'clavos_td' => 'required|boolean',
            'bolas_td' => 'required|boolean',
            'corta_td' => 'required|boolean',
            'desgas_td' => 'required|boolean',
            'golpes_ti' => 'required|boolean',
            'clavos_ti' => 'required|boolean',
            'bolas_ti' => 'required|boolean',
            'corta_ti' => 'required|boolean',
            'desgas_ti' => 'required|boolean',
            'golpes_ref' => 'required|boolean',
            'clavos_ref' => 'required|boolean',
            'bolas_ref' => 'required|boolean',
            'corta_ref' => 'required|boolean',
            'desgas_ref' => 'required|boolean',
            'ck_final' => 'nullable|boolean',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $response = [
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors,
        ];
        throw new HttpResponseException(response()->json($response, HttpCodes::HTTP_UNPROCESSABLE_ENTITY));
    }
}
