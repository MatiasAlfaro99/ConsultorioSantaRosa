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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            
            // Datos del Archivo
            $table->string('nombre'); 
            $table->string('ruta_archivo')->unique(); 
            $table->string('categoria')->nullable(); 
            $table->string('tipo_mime'); 
            
            // Auditoría (Quién lo subió)
            $table->foreignId('subido_por_id')->constrained('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
