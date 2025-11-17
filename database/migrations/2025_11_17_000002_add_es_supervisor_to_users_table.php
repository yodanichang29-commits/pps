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
            // Solo agregar el campo si no existe
            if (!Schema::hasColumn('users', 'es_supervisor')) {
                $table->boolean('es_supervisor')
                    ->default(false)
                    ->after('rol')
                    ->comment('Indica si un admin también puede supervisar estudiantes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'es_supervisor')) {
                $table->dropColumn('es_supervisor');
            }
        });
    }
};
