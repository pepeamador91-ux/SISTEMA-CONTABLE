<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteRelacionado extends Model
{
    protected $guarded = [];

    /**
     * Relación inversa: La relación pertenece a un comprobante principal.
     */
    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class);
    }
}