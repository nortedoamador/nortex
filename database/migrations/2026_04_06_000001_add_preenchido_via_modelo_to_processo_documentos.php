<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->boolean('preenchido_via_modelo')->default(false)->after('declaracao_anexo_5h');
        });

        if (Schema::hasTable('processo_documentos')) {
            DB::table('processo_documentos')->where('declaracao_residencia_2g', true)->update(['preenchido_via_modelo' => true]);
            DB::table('processo_documentos')->where('declaracao_anexo_5h', true)->update(['preenchido_via_modelo' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->dropColumn('preenchido_via_modelo');
        });
    }
};
