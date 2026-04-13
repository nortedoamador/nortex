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
            $table->string('tipo_propulsao', 32)->nullable()->after('compartimentos');
        });

        if (Schema::hasColumn('embarcacoes', 'motores')) {
            DB::table('embarcacoes')->orderBy('id')->select(['id', 'motores', 'marca_motor', 'potencia_maxima_motor', 'numero_motor'])->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    if (! self::rowTemDadosMotor($row)) {
                        continue;
                    }
                    DB::table('embarcacoes')->where('id', $row->id)->update(['tipo_propulsao' => 'motor']);
                }
            });
        }
    }

    private static function rowTemDadosMotor(object $row): bool
    {
        foreach (['marca_motor', 'potencia_maxima_motor', 'numero_motor'] as $col) {
            $v = $row->{$col} ?? null;
            if ($v !== null && trim((string) $v) !== '') {
                return true;
            }
        }
        $raw = $row->motores ?? null;
        if (! is_string($raw) || $raw === '') {
            return false;
        }
        $dec = json_decode($raw, true);
        if (! is_array($dec)) {
            return false;
        }
        foreach ($dec as $m) {
            if (! is_array($m)) {
                continue;
            }
            $s = trim((string) ($m['marca'] ?? '').(string) ($m['potencia'] ?? '').(string) ($m['numero_serie'] ?? ''));
            if ($s !== '') {
                return true;
            }
        }

        return false;
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn('tipo_propulsao');
        });
    }
};
