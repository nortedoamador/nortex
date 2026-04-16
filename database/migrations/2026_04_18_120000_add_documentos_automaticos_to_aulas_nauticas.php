<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aulas_nauticas', function (Blueprint $table) {
            if (! Schema::hasColumn('aulas_nauticas', 'documentos_automaticos')) {
                $table->json('documentos_automaticos')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('aulas_nauticas', function (Blueprint $table) {
            if (Schema::hasColumn('aulas_nauticas', 'documentos_automaticos')) {
                $table->dropColumn('documentos_automaticos');
            }
        });
    }
};
