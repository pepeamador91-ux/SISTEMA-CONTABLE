<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('version', 5)->default('4.0');
            $table->string('serie')->nullable();
            $table->string('folio')->nullable();
            $table->dateTime('fecha_emision');
            $table->dateTime('fecha_certificacion')->nullable();
            
            // Datos Emisor
            $table->string('rfc_emisor', 15)->index();
            $table->string('nombre_emisor')->nullable();
            $table->string('regimen_fiscal_emisor', 5)->nullable();
            
            // Datos Receptor (CFDI 4.0 requeridos)
            $table->string('rfc_receptor', 15)->index();
            $table->string('nombre_receptor')->nullable();
            $table->string('regimen_fiscal_receptor', 5)->nullable();
            $table->string('domicilio_fiscal_receptor', 10)->nullable(); // CP del receptor
            $table->string('uso_cfdi', 5)->nullable();
            
            // Atributos CFDI
            $table->string('lugar_expedicion', 10)->nullable(); // CP emisor
            $table->string('metodo_pago', 5)->nullable();
            $table->string('forma_pago', 5)->nullable();
            $table->string('tipo_relacion', 5)->nullable(); // Código de relación (01, 04, etc)
            $table->string('tipo_comprobante', 2); // I, E, P, T, N
            $table->string('moneda', 5)->default('MXN');
            $table->decimal('tipo_cambio', 18, 4)->default(1);
            $table->string('exportacion', 5)->nullable();
            
            // Importes Totales
            $table->decimal('subtotal', 18, 2);
            $table->decimal('descuento', 18, 2)->default(0);
            $table->decimal('total_impuestos_trasladados', 18, 2)->default(0);
            $table->decimal('total_impuestos_retenidos', 18, 2)->default(0);
            $table->decimal('total', 18, 2);
            
            // Auditoría y Datos SAT
            $table->text('sello')->nullable();
            $table->string('no_certificado', 30)->nullable();
            $table->string('estatus_sat')->default('Vigente');
            $table->longText('xml_storage')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};