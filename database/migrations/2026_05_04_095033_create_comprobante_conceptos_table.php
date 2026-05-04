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
    Schema::create('comprobante_conceptos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('comprobante_id')->constrained('comprobantes')->onDelete('cascade');
        $table->string('clave_prod_serv');
        $table->string('no_identificacion')->nullable();
        $table->integer('cantidad');
        $table->string('clave_unidad');
        $table->string('unidad')->nullable();
        $table->text('descripcion');
        $table->string('objeto_imp', 5)->nullable(); // Obligatorio en 4.0
        $table->decimal('valor_unitario', 15, 2);
        $table->decimal('importe', 15, 2);
        $table->decimal('descuento', 15, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_conceptos');
    }
};
