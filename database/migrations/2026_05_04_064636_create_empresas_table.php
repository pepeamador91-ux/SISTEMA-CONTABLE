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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social');
            $table->string('rfc', 13)->unique();
            
            // Datos sensibles cifrados (usamos text porque el cifrado genera cadenas largas)
            $table->text('ciec')->nullable(); 
            $table->text('fiel_cer_path')->nullable(); 
            $table->text('fiel_key_path')->nullable(); 
            $table->text('fiel_password')->nullable(); 
            
            $table->timestamps();
            $table->softDeletes(); // Para el historial de eliminaciones que pediste
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};