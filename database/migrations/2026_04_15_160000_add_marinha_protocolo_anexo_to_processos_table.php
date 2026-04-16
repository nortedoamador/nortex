<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            if (! Schema::hasColumn('processos', 'marinha_protocolo_anexo_path')) {
                $table->string('marinha_protocolo_anexo_path', 512)->nullable()->after('marinha_protocolo_data');
            }
            if (! Schema::hasColumn('processos', 'marinha_protocolo_anexo_original_name')) {
                $table->string('marinha_protocolo_anexo_original_name', 255)->nullable()->after('marinha_protocolo_anexo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $cols = collect(['marinha_protocolo_anexo_path', 'marinha_protocolo_anexo_original_name'])
                ->filter(fn (string $c) => Schema::hasColumn('processos', $c))
                ->all();
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
