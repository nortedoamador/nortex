<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->foreignId('embarcacao_id')
                ->nullable()
                ->after('cliente_id')
                ->constrained('embarcacoes')
                ->nullOnDelete();

            $table->index(['empresa_id', 'embarcacao_id'], 'processos_empresa_embarcacao_idx');
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropIndex('processos_empresa_embarcacao_idx');
            $table->dropConstrainedForeignId('embarcacao_id');
        });
    }
};

