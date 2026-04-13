<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processo_post_its', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('conteudo');
            $table->timestamps();
        });

        if (Schema::hasColumn('processos', 'observacoes')) {
            DB::table('processos')
                ->whereNotNull('observacoes')
                ->where('observacoes', '!=', '')
                ->orderBy('id')
                ->chunkById(100, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('processo_post_its')->insert([
                            'processo_id' => $row->id,
                            'user_id' => null,
                            'conteudo' => $row->observacoes,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                });

            DB::table('processos')->whereNotNull('observacoes')->update(['observacoes' => null]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('processo_post_its');
    }
};
