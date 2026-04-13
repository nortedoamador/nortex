<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->string('categoria', 32)->nullable()->after('slug')->index();
        });

        $chaSlugs = ['cha-primeira-habilitacao', 'cha-renovacao', 'cha-extravio-roubo-furto-dano', 'habilitacao'];
        $embSlugs = [
            'inscricao-embarcacao',
            'renovacao-tie-tiem',
            'segunda-via-tie-tiem',
            'transferencia-proprietario',
            'transferencia-jurisdicao-embarcacao',
        ];

        DB::table('tipo_processos')->whereIn('slug', $chaSlugs)->update(['categoria' => 'cha']);
        DB::table('tipo_processos')->whereIn('slug', $embSlugs)->update(['categoria' => 'embarcacao']);
        DB::table('tipo_processos')->where('slug', 'like', 'cir-%')->update(['categoria' => 'cir']);
    }

    public function down(): void
    {
        Schema::table('tipo_processos', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }
};
