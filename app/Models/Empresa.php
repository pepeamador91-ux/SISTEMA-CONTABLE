<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes; // Mantiene el historial en la base de datos

    protected $fillable = [
        'razon_social', 
        'rfc', 
        'ciec', 
        'fiel_cer_path', 
        'fiel_key_path', 
        'fiel_password'
    ];

    // Cifrado automático para CIEC y Password de FIEL
    protected $casts = [
        'ciec' => 'encrypted',
        'fiel_password' => 'encrypted',
    ];
}