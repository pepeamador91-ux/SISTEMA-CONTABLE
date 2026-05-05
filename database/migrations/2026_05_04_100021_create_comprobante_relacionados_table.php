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
    Schema::create('comprobante_relacionados', function (Blueprint $table) {
        $table->id();
        // Relación con nuestra tabla principal
        $table->foreignId('comprobante_id')->constrained('comprobantes')->onDelete('cascade');
        
        // El UUID que viene dentro del nodo CfdiRelacionado
        $table->uuid('uuid_relacionado')->index();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_relacionados');
    }
};
