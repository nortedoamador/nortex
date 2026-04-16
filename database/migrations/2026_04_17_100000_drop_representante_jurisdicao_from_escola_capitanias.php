<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $toDrop = collect(['representante_jurisdicao', 'representante_endereco'])
            ->filter(fn (string $c) => Schema::hasColumn('escola_capitanias', $c))
            ->values()
            ->all();

        if ($toDrop !== []) {
            Schema::table('escola_capitanias', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }
    }

    public function down(): void
    {
        Schema::table('escola_capitanias', function (Blueprint $table) {
            $table->string('representante_jurisdicao', 255)->nullable()->after('representante_nome');
            $table->text('representante_endereco')->nullable()->after('representante_jurisdicao');
        });
    }
};
