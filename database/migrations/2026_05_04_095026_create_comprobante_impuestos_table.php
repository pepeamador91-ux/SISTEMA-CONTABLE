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
    Schema::create('comprobante_impuestos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('comprobante_id')->constrained('comprobantes')->onDelete('cascade');
        $table->decimal('base', 15, 2)->default(0); // Indispensable para auditoría
        $table->enum('tipo', ['Traslado', 'Retencion']);
        $table->string('impuesto', 3); 
        $table->string('tipo_factor'); 
        $table->decimal('tasa_o_cuota', 15, 6); 
        $table->decimal('importe', 15, 2);
        $table->timestamps();
    });
}    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_impuestos');
    }
}; 
