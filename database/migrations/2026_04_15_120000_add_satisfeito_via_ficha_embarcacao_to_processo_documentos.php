<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->boolean('satisfeito_via_ficha_embarcacao')->default(false)->after('preenchido_via_modelo');
        });
    }

    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->dropColumn('satisfeito_via_ficha_embarcacao');
        });
    }
};
