<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class A_Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory, HasApiTokens;

    protected $table = 'a_usuario';

    protected $hidden = [
        'password'
    ];

    public function getRoles() : Collection
    {
        $roles = DB::table('a_usuario_roles_old')
            ->select('a_roles.id','a_roles.nombre')
            ->join('a_roles', 'a_roles.id', '=', 'a_usuario_roles_old.id_rol')
            ->where('a_usuario_roles_old.vivo', true)
            ->where('id_usuario', $this->id)
            ->orderBy('a_roles.id')->get();

        return collect($roles);
    }

    public function getSucursales() : Collection
    {
        $sucursales = DB::table('a_usuario_sucursal_old')
            ->select('a_sucursal.id', 'a_sucursal.nombre')
            ->join('a_sucursal', 'a_sucursal.id', '=', 'a_usuario_sucursal_old.id_sucursal')
            ->where('a_usuario_sucursal_old.vivo', true)
            ->where('id_usuario', $this->id)->get();

        return collect($sucursales);
    }

    /**
	 * Get the identifier that will be stored in the subject claim of the JWT.
	 * @return mixed
	 */
	public function getJWTIdentifier() {
        return $this->getKey();
	}

	/**
	 * Return a key value array, containing any custom claims to be added to the JWT.
	 * @return array
	 */
	public function getJWTCustomClaims() {
        return [];
	}
}
