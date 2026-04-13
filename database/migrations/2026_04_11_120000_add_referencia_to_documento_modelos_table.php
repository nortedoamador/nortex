<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_modelos', function (Blueprint $table) {
            $table->string('referencia', 160)->nullable()->after('titulo');
        });
    }

    public function down(): void
    {
        Schema::table('documento_modelos', function (Blueprint $table) {
            $table->dropColumn('referencia');
        });
    }
};
