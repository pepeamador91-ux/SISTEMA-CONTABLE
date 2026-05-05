<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Datos Generales del Recibo de Nómina
        Schema::create('comprobante_nominas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes')->onDelete('cascade');
            $table->string('tipo_nomina', 5); // O (Ordinaria), E (Extraordinaria)
            $table->dateTime('fecha_pago');
            $table->dateTime('fecha_inicial_pago');
            $table->dateTime('fecha_final_pago');
            $table->decimal('num_dias_pagados', 18, 3);
            
            // Datos del Trabajador
            $table->string('curp', 20);
            $table->string('num_seguridad_social')->nullable();
            $table->date('fecha_inicio_rel_laboral')->nullable();
            $table->string('antiguedad')->nullable();
            $table->string('tipo_contrato', 5)->nullable();
            $table->string('tipo_jornada', 5)->nullable();
            $table->string('tipo_regimen', 5)->nullable();
            $table->string('num_empleado')->nullable();
            $table->string('departamento')->nullable();
            $table->string('puesto')->nullable();
            $table->string('riesgo_puesto', 5)->nullable();
            $table->string('periodicidad_pago', 5)->nullable();
            $table->string('banco', 5)->nullable();
            $table->string('cuenta_bancaria')->nullable();
            $table->decimal('salario_base_cot_apor', 18, 2)->nullable();
            $table->decimal('sd_i', 18, 2)->nullable(); // Salario Diario Integrado
            $table->string('clave_ent_fed', 5)->nullable();
            $table->timestamps();
        });

        // 2. Percepciones (Ingresos)
        Schema::create('comprobante_nomina_percepciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_nomina_id')->constrained('comprobante_nominas')->onDelete('cascade');
            $table->string('tipo_percepcion', 5);
            $table->string('clave');
            $table->string('concepto');
            $table->decimal('importe_gravado', 18, 2);
            $table->decimal('importe_exento', 18, 2);
            $table->timestamps();
        });

        // 3. Deducciones (Egresos/Retenciones)
        Schema::create('comprobante_nomina_deducciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_nomina_id')->constrained('comprobante_nominas')->onDelete('cascade');
            $table->string('tipo_deduccion', 5);
            $table->string('clave');
            $table->string('concepto');
            $table->decimal('importe', 18, 2);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('comprobante_nomina_deducciones');
        Schema::dropIfExists('comprobante_nomina_percepciones');
        Schema::dropIfExists('comprobante_nominas');
    }
};