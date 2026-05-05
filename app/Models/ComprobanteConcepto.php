<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobanteConcepto extends Model
{
    protected $guarded = [];

    public function comprobante() {
        return $this->belongsTo(Comprobante::class);
    }
}