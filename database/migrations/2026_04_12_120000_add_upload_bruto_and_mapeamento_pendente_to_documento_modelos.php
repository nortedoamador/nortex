<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_modelos', function (Blueprint $table) {
            $table->longText('conteudo_upload_bruto')->nullable()->after('conteudo');
            $table->boolean('upload_mapeamento_pendente')->default(false)->after('conteudo_upload_bruto');
        });

        if (Schema::hasColumn('documento_modelos', 'conteudo_upload_bruto')) {
            DB::statement('UPDATE documento_modelos SET conteudo_upload_bruto = conteudo, upload_mapeamento_pendente = 0 WHERE conteudo_upload_bruto IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('documento_modelos', function (Blueprint $table) {
            $table->dropColumn(['conteudo_upload_bruto', 'upload_mapeamento_pendente']);
        });
    }
};
