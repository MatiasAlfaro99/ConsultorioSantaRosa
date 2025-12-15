<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Agregamos la columna 'categoria' después de 'titulo'
            // Le ponemos un valor por defecto para que no fallen los eventos viejos
            $table->string('categoria')->default('reunion')->after('titulo');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }
};
