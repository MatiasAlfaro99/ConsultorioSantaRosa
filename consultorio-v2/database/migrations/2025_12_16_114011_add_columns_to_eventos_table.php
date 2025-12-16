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
        Schema::table('eventos', function (Blueprint $table) {
            // Verificamos si las columnas existen antes de agregarlas
            if (!Schema::hasColumn('eventos', 'lugar')) {
                $table->string('lugar')->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('eventos', 'categoria')) {
                $table->string('categoria')->nullable()->after('lugar');
            }
            if (!Schema::hasColumn('eventos', 'comunicado_id')) {
                $table->foreignId('comunicado_id')
                      ->nullable()
                      ->constrained('comunicados')
                      ->onDelete('cascade')
                      ->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Para borrar, primero eliminamos la llave foránea y luego las columnas
            if (Schema::hasColumn('eventos', 'comunicado_id')) {
                $table->dropForeign(['comunicado_id']);
                $table->dropColumn('comunicado_id');
            }
            if (Schema::hasColumn('eventos', 'lugar')) {
                $table->dropColumn('lugar');
            }
            if (Schema::hasColumn('eventos', 'categoria')) {
                $table->dropColumn('categoria');
            }
        });
    }
};
