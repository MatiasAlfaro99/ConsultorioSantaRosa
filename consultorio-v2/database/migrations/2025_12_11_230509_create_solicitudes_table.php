<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            
            // 1. Quien solicita y qué solicita
            // Changed from funcionario_id to user_id to match Model
            $table->foreignId('user_id')->constrained('users');
            
            // Changed from enum to string to allow more flexible types as per requirements
            $table->string('tipo'); 
            
            // New fields required by Model
            $table->boolean('es_por_horas')->default(false);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('dias_solicitados'); 
            $table->text('motivo')->nullable();

            // 2. Estado del Flujo (Máquina de Estados)
            // estados: pendiente, aprobado_jefatura, aprobado_final, rechazado
            $table->string('estado')->default('pendiente');

            // 3. Auditoría de Aprobación Nivel 1 (Jefatura)
            $table->foreignId('jefe_aprobador_id')->nullable()->constrained('users');
            $table->dateTime('fecha_aprobacion_jefe')->nullable();

            // 4. Auditoría de Aprobación Nivel 2 (Dirección)
            $table->foreignId('director_aprobador_id')->nullable()->constrained('users');
            $table->dateTime('fecha_aprobacion_director')->nullable();

            // 5. Motivo de rechazo (Feedback)
            // Renamed from observacion_rechazo to razon_rechazo to match Model
            $table->text('razon_rechazo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
