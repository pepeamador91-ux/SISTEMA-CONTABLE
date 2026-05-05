<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Importamos SoftDeletes

class User extends Authenticatable
{
    // 2. Añadimos SoftDeletes al uso de la clase
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Añadido para permitir el registro de roles
    ];

    /**
     * CENTRALIZACIÓN DE ROLES
     * Definimos los roles aquí para que sea fácil agregar más (ej. 'contador') en el futuro.
     */
    public static $roles = [
        'admin'   => 'Administrador',
        'usuario' => 'Usuario Estándar',
        'invitado' => 'Invitado',
    ];

    /**
     * Accessor para obtener el nombre legible del rol.
     * Uso: $user->role_name
     */
    public function getRoleNameAttribute()
    {
        return self::$roles[$this->role] ?? 'Sin Rol';
    }

    /**
     * Los atributos que deben permanecer ocultos para los arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión de tipos de atributos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime', // 3. Aseguramos que la fecha de eliminación se trate como objeto Carbon
        ];
    }
}