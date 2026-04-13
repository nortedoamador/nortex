<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processo_documento_anexos', function (Blueprint $table) {
            $table->string('extra_validation_status', 32)->default('pendente')->after('tamanho');
            $table->text('extra_validation_notes')->nullable()->after('extra_validation_status');
            $table->timestamp('validated_at')->nullable()->after('extra_validation_notes');
        });

        Schema::table('processo_documentos', function (Blueprint $table) {
            $table->date('data_validade_documento')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        $cols = collect(['data_validade_documento', 'data_validade_referencia'])
            ->filter(fn (string $c) => Schema::hasColumn('processo_documentos', $c))
            ->values()
            ->all();
        if ($cols !== []) {
            Schema::table('processo_documentos', function (Blueprint $table) use ($cols) {
                $table->dropColumn($cols);
            });
        }

        Schema::table('processo_documento_anexos', function (Blueprint $table) {
            $table->dropColumn(['extra_validation_status', 'extra_validation_notes', 'validated_at']);
        });
    }
};
