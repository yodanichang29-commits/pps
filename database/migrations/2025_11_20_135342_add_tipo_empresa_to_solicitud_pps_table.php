<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = 'solicitud_p_p_s';

        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Solo agregar si no existe
                if (!Schema::hasColumn($tableName, 'tipo_empresa')) {
                    $table->enum('tipo_empresa', ['publica', 'privada'])
                          ->nullable()
                          ->after('nombre_empresa');
                }
            });
        }
    }

    public function down(): void
    {
        $tableName = 'solicitud_p_p_s';

        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'tipo_empresa')) {
                    $table->dropColumn('tipo_empresa');
                }
            });
        }
    }
};