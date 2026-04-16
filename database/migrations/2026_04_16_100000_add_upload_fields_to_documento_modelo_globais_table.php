<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_modelo_globais', function (Blueprint $table) {
            $table->longText('conteudo_upload_bruto')->nullable()->after('conteudo');
            $table->boolean('upload_mapeamento_pendente')->default(false)->after('conteudo_upload_bruto');
            $table->json('mapeamento_upload')->nullable()->after('upload_mapeamento_pendente');
        });
    }

    public function down(): void
    {
        Schema::table('documento_modelo_globais', function (Blueprint $table) {
            $table->dropColumn(['conteudo_upload_bruto', 'upload_mapeamento_pendente', 'mapeamento_upload']);
        });
    }
};
