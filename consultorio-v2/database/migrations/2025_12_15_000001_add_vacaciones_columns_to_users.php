<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'vacaciones_total')) {
                $table->integer('vacaciones_total')->default(15)->after('password');
            }
            if (! Schema::hasColumn('users', 'vacaciones_usadas')) {
                $table->integer('vacaciones_usadas')->default(0)->after('vacaciones_total');
            }
            if (! Schema::hasColumn('users', 'dias_admin_usados')) {
                $table->integer('dias_admin_usados')->default(0)->after('vacaciones_usadas');
            }
            if (! Schema::hasColumn('users', 'region_sur')) {
                $table->boolean('region_sur')->nullable()->default(false)->after('dias_admin_usados');
            }
            if (! Schema::hasColumn('users', 'jornada_sabado')) {
                $table->boolean('jornada_sabado')->nullable()->default(false)->after('region_sur');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'jornada_sabado')) {
                $table->dropColumn('jornada_sabado');
            }
            if (Schema::hasColumn('users', 'region_sur')) {
                $table->dropColumn('region_sur');
            }
            if (Schema::hasColumn('users', 'dias_admin_usados')) {
                $table->dropColumn('dias_admin_usados');
            }
            if (Schema::hasColumn('users', 'vacaciones_usadas')) {
                $table->dropColumn('vacaciones_usadas');
            }
            if (Schema::hasColumn('users', 'vacaciones_total')) {
                $table->dropColumn('vacaciones_total');
            }
        });
    }
};
