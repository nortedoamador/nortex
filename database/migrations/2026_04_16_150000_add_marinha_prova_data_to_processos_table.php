<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            if (! Schema::hasColumn('processos', 'marinha_prova_data')) {
                $table->date('marinha_prova_data')->nullable()->after('marinha_protocolo_anexo_original_name');
            }
        });

        Schema::table('processos', function (Blueprint $table) {
            if (Schema::hasColumn('processos', 'marinha_prova_data')) {
                $table->index(['empresa_id', 'status', 'marinha_prova_data'], 'processos_empresa_status_prova_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            if (Schema::hasColumn('processos', 'marinha_prova_data')) {
                $table->dropIndex('processos_empresa_status_prova_idx');
            }
        });

        Schema::table('processos', function (Blueprint $table) {
            if (Schema::hasColumn('processos', 'marinha_prova_data')) {
                $table->dropColumn('marinha_prova_data');
            }
        });
    }
};
