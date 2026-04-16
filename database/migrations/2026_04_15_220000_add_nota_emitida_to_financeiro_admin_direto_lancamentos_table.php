<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('financeiro_admin_direto_lancamentos')) {
            return;
        }

        if (Schema::hasColumn('financeiro_admin_direto_lancamentos', 'nota_emitida')) {
            return;
        }

        Schema::table('financeiro_admin_direto_lancamentos', function (Blueprint $table) {
            $table->boolean('nota_emitida')->default(false)->after('comprovante_path');
            $table->index(['empresa_id', 'nota_emitida'], 'fin_ad_lanc_emp_nota_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('financeiro_admin_direto_lancamentos')) {
            return;
        }

        if (! Schema::hasColumn('financeiro_admin_direto_lancamentos', 'nota_emitida')) {
            return;
        }

        Schema::table('financeiro_admin_direto_lancamentos', function (Blueprint $table) {
            $table->dropIndex('fin_ad_lanc_emp_nota_idx');
            $table->dropColumn('nota_emitida');
        });
    }
};
