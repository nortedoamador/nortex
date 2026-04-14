<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'cliente_anexos',
            'embarcacao_anexos',
            'habilitacao_anexos',
            'processo_documento_anexos',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id')->unique();
            });

            DB::table($tableName)
                ->whereNull('uuid')
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($tableName) {
                    foreach ($rows as $row) {
                        DB::table($tableName)
                            ->where('id', $row->id)
                            ->update(['uuid' => (string) Str::uuid()]);
                    }
                });
        }
    }

    public function down(): void
    {
        foreach ([
            'cliente_anexos',
            'embarcacao_anexos',
            'habilitacao_anexos',
            'processo_documento_anexos',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropUnique($tableName.'_uuid_unique');
                $table->dropColumn('uuid');
            });
        }
    }
};
