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
        Schema::create('licencias_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Funcionario
            $table->foreignId('subdirector_id')->constrained('users')->onDelete('cascade'); // Subdirector que ingresa
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('nombre_archivo');
            $table->text('observaciones')->nullable();
            $table->string('estado')->default('ingresada'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licencias_medicas');
    }
};
