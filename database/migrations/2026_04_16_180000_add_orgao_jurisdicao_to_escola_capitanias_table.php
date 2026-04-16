<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('escola_capitanias', function (Blueprint $table) {
            $table->string('orgao_jurisdicao', 255)->nullable()->after('nome_capitao_portos');
        });
    }

    public function down(): void
    {
        Schema::table('escola_capitanias', function (Blueprint $table) {
            $table->dropColumn('orgao_jurisdicao');
        });
    }
};
