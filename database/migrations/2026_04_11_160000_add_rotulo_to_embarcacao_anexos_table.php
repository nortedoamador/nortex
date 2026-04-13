<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embarcacao_anexos', function (Blueprint $table) {
            $table->string('rotulo', 255)->nullable()->after('nome_original');
        });
    }

    public function down(): void
    {
        Schema::table('embarcacao_anexos', function (Blueprint $table) {
            $table->dropColumn('rotulo');
        });
    }
};
