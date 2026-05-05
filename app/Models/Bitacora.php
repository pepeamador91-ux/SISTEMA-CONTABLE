<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $fillable = [
        'user_id', 
        'accion', 
        'modelo', 
        'modelo_id', 
        'datos_anteriores', 
        'datos_nuevos', 
        'ip_address'
    ];

    // Esto permite que Laravel maneje los campos JSON como arreglos automáticamente
    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
    ];
}