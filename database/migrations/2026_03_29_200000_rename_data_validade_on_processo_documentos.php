<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instalações que rodaram 140000 antes da coluna se chamar data_validade_documento.
 * Instalações novas: 140000 já cria data_validade_documento — esta migration não faz nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('processo_documentos')) {
            return;
        }

        if (Schema::hasColumn('processo_documentos', 'data_validade_referencia')
            && ! Schema::hasColumn('processo_documentos', 'data_validade_documento')) {
            Schema::table('processo_documentos', function (Blueprint $table) {
                $table->renameColumn('data_validade_referencia', 'data_validade_documento');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('processo_documentos')) {
            return;
        }

        if (Schema::hasColumn('processo_documentos', 'data_validade_documento')
            && ! Schema::hasColumn('processo_documentos', 'data_validade_referencia')) {
            Schema::table('processo_documentos', function (Blueprint $table) {
                $table->renameColumn('data_validade_documento', 'data_validade_referencia');
            });
        }
    }
};
