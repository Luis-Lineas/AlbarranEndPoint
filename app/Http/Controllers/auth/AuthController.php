<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginWithNomina(LoginRequest $request)
    {
        $request->authenticate();

        if (Auth::user() != null ) {
            $token = JWTAuth::fromUser(Auth::user());

            $roles = DB::table('a_usuario_roles_old')
            ->select('a_roles.id','a_roles.nombre')
            ->join('a_roles', 'a_roles.id', '=', 'a_usuario_roles_old.id_rol')
            ->where('a_usuario_roles_old.vivo', true)
            ->where('id_usuario', Auth::id())
            ->orderBy('a_roles.id')->get();

            $sucursales = DB::table('a_usuario_sucursal_old')
            ->select('a_sucursal.id', 'a_sucursal.nombre')
            ->join('a_sucursal', 'a_sucursal.id', '=', 'a_usuario_sucursal_old.id_sucursal')
            ->where('a_usuario_sucursal_old.vivo', true)
            ->where('id_usuario', Auth::id())->get();

            $response = (object)[
                'id' => Auth::id(),
                'username' => Auth::user()->nombre,
                'roles' => $roles,
                'sucursales' => $sucursales,
                'jsonToken' => $token
            ];
            return response()->json($response);
        }

        return response()->json(['error' => 'Credenciales inválidas'], 401);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $roles = DB::table('a_usuario_roles_old')
            ->select('a_roles.id','a_roles.nombre')
            ->join('a_roles', 'a_roles.id', '=', 'a_usuario_roles_old.id_rol')
            ->where('a_usuario_roles_old.vivo', true)
            ->where('id_usuario', Auth::id())
            ->orderBy('a_roles.id')->get();

            $sucursales = DB::table('a_usuario_sucursal_old')
            ->select('a_sucursal.id', 'a_sucursal.nombre')
            ->join('a_sucursal', 'a_sucursal.id', '=', 'a_usuario_sucursal_old.id_sucursal')
            ->where('a_usuario_sucursal_old.vivo', true)
            ->where('id_usuario', Auth::id())->get();

            $response = (object)[
                'id' => Auth::id(),
                'username' => Auth::user()->nombre,
                'roles' => $roles,
                'sucursales' => $sucursales
            ];
            return response()->json($response);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
