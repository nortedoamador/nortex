<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_tipos', function (Blueprint $table) {
            $table->text('nome')->change();
        });
    }

    public function down(): void
    {
        Schema::table('documento_tipos', function (Blueprint $table) {
            $table->string('nome')->change();
        });
    }
};
