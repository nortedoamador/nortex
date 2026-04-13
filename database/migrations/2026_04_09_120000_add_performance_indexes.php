<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->index(['empresa_id', 'status', 'updated_at'], 'processos_empresa_status_updated_idx');
            $table->index(['empresa_id', 'updated_at'], 'processos_empresa_updated_idx');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->index(['empresa_id', 'nome'], 'clientes_empresa_nome_idx');
        });

        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->index(['processo_id', 'status'], 'proc_docs_processo_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->dropIndex('proc_docs_processo_status_idx');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('clientes_empresa_nome_idx');
        });

        Schema::table('processos', function (Blueprint $table) {
            $table->dropIndex('processos_empresa_status_updated_idx');
            $table->dropIndex('processos_empresa_updated_idx');
        });
    }
};

