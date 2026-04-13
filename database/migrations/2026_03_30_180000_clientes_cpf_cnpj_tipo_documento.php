<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('tipo_documento', 2)->nullable()->after('cpf');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->string('cpf', 20)->nullable()->change();
            $table->string('rg', 40)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('tipo_documento');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->string('cpf', 14)->nullable()->change();
            $table->string('rg', 32)->nullable()->change();
        });
    }
};
