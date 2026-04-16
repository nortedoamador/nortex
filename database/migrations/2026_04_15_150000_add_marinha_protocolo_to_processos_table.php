<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            if (! Schema::hasColumn('processos', 'marinha_protocolo_numero')) {
                $table->string('marinha_protocolo_numero', 255)->nullable()->after('jurisdicao');
            }
            if (! Schema::hasColumn('processos', 'marinha_protocolo_data')) {
                $table->date('marinha_protocolo_data')->nullable()->after('marinha_protocolo_numero');
            }
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $cols = collect(['marinha_protocolo_numero', 'marinha_protocolo_data'])
                ->filter(fn (string $c) => Schema::hasColumn('processos', $c))
                ->all();
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
