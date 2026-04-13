<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->string('tipo_navegacao', 32)->nullable()->after('atividade');
        });

        if (Schema::hasColumn('embarcacoes', 'area_navegacao')) {
            DB::table('embarcacoes')->orderBy('id')->select(['id', 'area_navegacao'])->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $area = $row->area_navegacao;
                    if ($area === null || $area === '') {
                        continue;
                    }
                    if ($area === 'interior') {
                        DB::table('embarcacoes')->where('id', $row->id)->update(['tipo_navegacao' => 'interior']);
                    } elseif (in_array($area, ['costeira', 'oceanica'], true)) {
                        DB::table('embarcacoes')->where('id', $row->id)->update(['tipo_navegacao' => 'mar_aberto']);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn('tipo_navegacao');
        });
    }
};
