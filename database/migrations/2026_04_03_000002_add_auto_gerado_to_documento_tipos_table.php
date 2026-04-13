<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_tipos', function (Blueprint $table) {
            $table->boolean('auto_gerado')->default(false)->after('nome');
            $table->string('modelo_slug', 80)->nullable()->after('auto_gerado');
        });
    }

    public function down(): void
    {
        Schema::table('documento_tipos', function (Blueprint $table) {
            $table->dropColumn(['auto_gerado', 'modelo_slug']);
        });
    }
};

