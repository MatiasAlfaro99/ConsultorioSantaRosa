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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('vacaciones_total')->default(15);
            $table->integer('vacaciones_usadas')->default(0);
            $table->integer('dias_admin_usados')->default(0);
            $table->boolean('region_sur')->default(false);
            $table->boolean('jornada_sabado')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['vacaciones_total', 'vacaciones_usadas', 'dias_admin_usados', 'region_sur', 'jornada_sabado']);
        });
    }
};
