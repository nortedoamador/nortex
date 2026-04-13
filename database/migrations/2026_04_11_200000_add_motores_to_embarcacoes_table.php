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
            $table->json('motores')->nullable()->after('numero_motor');
        });

        $rows = DB::table('embarcacoes')->select(['id', 'marca_motor', 'potencia_maxima_motor', 'numero_motor', 'motores'])->get();

        foreach ($rows as $row) {
            if ($row->motores !== null && $row->motores !== '') {
                continue;
            }
            $marca = trim((string) ($row->marca_motor ?? ''));
            $pot = trim((string) ($row->potencia_maxima_motor ?? ''));
            $num = trim((string) ($row->numero_motor ?? ''));
            if ($marca === '' && $pot === '' && $num === '') {
                continue;
            }
            DB::table('embarcacoes')->where('id', $row->id)->update([
                'motores' => json_encode([[
                    'marca' => $marca,
                    'potencia' => $pot,
                    'numero_serie' => $num,
                ]]),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn('motores');
        });
    }
};
