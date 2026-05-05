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
    Schema::create('bitacoras', function (Blueprint $table) {
        $table->id();
        // Relacionamos con el usuario que hace la acción
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        $table->string('accion'); // alta, edicion, eliminacion, etc.
        $table->string('modelo'); // Ejemplo: 'Empresa'
        $table->unsignedBigInteger('modelo_id'); // ID del registro afectado
        
        // Guardamos el "antes" y el "después" en formato JSON
        $table->json('datos_anteriores')->nullable();
        $table->json('datos_nuevos')->nullable();
        
        $table->string('ip_address')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};
