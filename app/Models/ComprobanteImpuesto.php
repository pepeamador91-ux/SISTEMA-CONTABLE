<?php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteImpuesto extends Model
{
    // Permitimos la carga masiva para procesar los XML rápido
    protected $guarded = [];

    /**
     * Relación inversa: Un impuesto pertenece a un comprobante.
     */
    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class);
    }
}