<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('comprobantes', function (Blueprint $table) {
        // Verificamos si existe 'uuid', si no, lo creamos primero
        if (!Schema::hasColumn('comprobantes', 'uuid')) {
            $table->uuid('uuid')->unique()->first(); // Lo pone al principio de la tabla
        }

        // Ahora añadimos lo demás sin riesgo de error por posición
        if (!Schema::hasColumn('comprobantes', 'tipo_comprobante')) {
            $table->string('tipo_comprobante', 1)->after('uuid');
        }
        
        if (!Schema::hasColumn('comprobantes', 'tipo_relacion')) {
            $table->string('tipo_relacion', 3)->nullable()->after('tipo_comprobante');
        }

        if (!Schema::hasColumn('comprobantes', 'deleted_at')) {
            $table->softDeletes(); 
        }
    });
}
};  