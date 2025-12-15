<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitudes', 'dias_descontados')) {
                $table->integer('dias_descontados')->nullable()->after('estado');
            }
            if (! Schema::hasColumn('solicitudes', 'fecha_reincorporacion')) {
                $table->date('fecha_reincorporacion')->nullable()->after('dias_descontados');
            }
        });
    }

    public function down()
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes', 'fecha_reincorporacion')) {
                $table->dropColumn('fecha_reincorporacion');
            }
            if (Schema::hasColumn('solicitudes', 'dias_descontados')) {
                $table->dropColumn('dias_descontados');
            }
        });
    }
};
