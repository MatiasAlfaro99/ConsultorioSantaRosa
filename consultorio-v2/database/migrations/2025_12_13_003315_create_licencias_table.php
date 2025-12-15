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
        Schema::create('licencias', function (Blueprint $table) {
            $table->id();

            // ¿A quién pertenece la licencia?
            $table->foreignId('funcionario_id')->constrained('users')->onDelete('cascade');

            // ¿Quién la subió? (Auditoría: Subdirector)
            $table->foreignId('creado_por_id')->constrained('users');

            $table->date('fecha_inicio');
            $table->integer('dias');
            $table->date('fecha_fin'); // Calculado o ingresado

            $table->string('tipo_licencia')->default('enfermedad_comun'); // Opcional: maternal, accidente, etc.

            // El archivo de respaldo (Ruta del PDF/Foto)
            $table->string('archivo_path');

            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licencias');
    }
};
