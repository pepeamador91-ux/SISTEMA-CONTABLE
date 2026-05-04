<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Cabecera del Pago (El movimiento de dinero)
        Schema::create('comprobante_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes')->onDelete('cascade');
            $table->dateTime('fecha_pago');
            $table->string('forma_pago_p', 5);
            $table->string('moneda_p', 5);
            $table->decimal('tipo_cambio_p', 18, 6)->nullable();
            $table->decimal('monto', 18, 2);
            $table->string('num_operacion')->nullable();
            $table->string('rfc_emisor_cta_ord', 15)->nullable();
            $table->string('nom_banco_ord_ext')->nullable();
            $table->string('cta_ordenante')->nullable();
            $table->string('rfc_emisor_cta_ben', 15)->nullable();
            $table->string('cta_beneficiario')->nullable();
            $table->timestamps();
        });

        // 2. Documentos Relacionados (Qué facturas se están pagando)
        Schema::create('comprobante_pago_relacionados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_pago_id')->constrained('comprobante_pagos')->onDelete('cascade');
            $table->uuid('id_documento')->index(); // UUID de la factura pagada
            $table->string('serie')->nullable();
            $table->string('folio')->nullable();
            $table->string('moneda_dr', 5);
            $table->decimal('equivalencia_dr', 18, 6)->nullable(); // CFDI 4.0
            $table->integer('num_parcialidad');
            $table->decimal('imp_saldo_ant', 18, 2);
            $table->decimal('imp_pagado', 18, 2);
            $table->decimal('imp_saldo_insoluto', 18, 2);
            $table->string('objeto_imp_dr', 5)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('comprobante_pago_relacionados');
        Schema::dropIfExists('comprobante_pagos');
    }
};