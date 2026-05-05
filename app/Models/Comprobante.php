<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comprobante extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     */
    protected $table = 'comprobantes';

    /**
     * Permitimos asignación masiva para facilitar el trabajo del Parser.
     * Esto incluye todos los campos de la migración: uuid, tipo_comprobante, rfc_emisor, etc.
     */
    protected $guarded = [];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * Esto garantiza que los cálculos contables y las fechas sean precisos.
     */
    protected $casts = [
        'fecha_emision'       => 'datetime',
        'fecha_certificacion' => 'datetime',
        'subtotal'            => 'decimal:2',
        'descuento'           => 'decimal:2',
        'total'               => 'decimal:2',
        'total_impuestos_trasladados' => 'decimal:2',
        'total_impuestos_retenidos'   => 'decimal:2',
        'tipo_cambio'         => 'decimal:4',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    /**
     * RELACIONES DEL SISTEMA
     */

    // Relación con los detalles del CFDI (Conceptos)
    public function conceptos() 
    {
        return $this->hasMany(ComprobanteConcepto::class);
    }

    // Relación con el desglose de IVA, ISR e IEPS
    public function impuestos() 
    {
        return $this->hasMany(ComprobanteImpuesto::class);
    }

    // Relación con CFDI relacionados (Sustituciones, Notas de Crédito, etc.)
    public function relacionados() 
    {
        return $this->hasMany(ComprobanteRelacionado::class);
    }

    // Relación con Complementos de Pago
    public function pagos() 
    {
        return $this->hasMany(ComprobantePago::class);
    }

    // Relación con Complemento de Nómina
    public function nomina() 
    {
        return $this->hasOne(ComprobanteNomina::class);
    }

    /**
     * SCOPES DE BÚSQUEDA (Para facilitar consultas en el Dashboard)
     */

    public function scopeIngresos($query)
    {
        return $query->where('tipo_comprobante', 'I');
    }

    public function scopeEgresos($query)
    {
        return $query->where('tipo_comprobante', 'E');
    }

    public function scopePagos($query)
    {
        return $query->where('tipo_comprobante', 'P');
    }

    public function scopeNominas($query)
    {
        return $query->where('tipo_comprobante', 'N');
    }
}