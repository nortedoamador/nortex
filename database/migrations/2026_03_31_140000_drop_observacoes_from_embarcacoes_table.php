<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('embarcacoes', 'observacoes')) {
            return;
        }

        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn('observacoes');
        });
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->text('observacoes')->nullable();
        });
    }
};
