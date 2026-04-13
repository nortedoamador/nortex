<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->string('escritura_cartorio', 120)->nullable()->after('nf_documento_vendedor');
            $table->string('escritura_numero', 64)->nullable()->after('escritura_cartorio');
            $table->date('escritura_data')->nullable()->after('escritura_numero');
        });
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn([
                'escritura_cartorio',
                'escritura_numero',
                'escritura_data',
            ]);
        });
    }
};

