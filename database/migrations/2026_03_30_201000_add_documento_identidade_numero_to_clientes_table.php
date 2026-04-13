<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('documento_identidade_numero', 40)->nullable()->after('documento_identidade_tipo');
        });

        // Backfill: mantém compatibilidade com dados antigos (coluna rg).
        DB::table('clientes')
            ->whereNull('documento_identidade_numero')
            ->whereNotNull('rg')
            ->update(['documento_identidade_numero' => DB::raw('rg')]);
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('documento_identidade_numero');
        });
    }
};

