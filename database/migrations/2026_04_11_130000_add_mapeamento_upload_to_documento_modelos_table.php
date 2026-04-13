<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_modelos', function (Blueprint $table) {
            $table->json('mapeamento_upload')->nullable()->after('conteudo');
        });
    }

    public function down(): void
    {
        Schema::table('documento_modelos', function (Blueprint $table) {
            $table->dropColumn('mapeamento_upload');
        });
    }
};
