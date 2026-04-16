<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('aula_nautica_escola_instrutores')) {
            return;
        }

        Schema::table('aula_nautica_escola_instrutores', function (Blueprint $table) {
            if (! Schema::hasColumn('aula_nautica_escola_instrutores', 'programa_atestado')) {
                $table->string('programa_atestado', 16)->default('ambos')->after('escola_instrutor_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('aula_nautica_escola_instrutores')) {
            return;
        }

        Schema::table('aula_nautica_escola_instrutores', function (Blueprint $table) {
            if (Schema::hasColumn('aula_nautica_escola_instrutores', 'programa_atestado')) {
                $table->dropColumn('programa_atestado');
            }
        });
    }
};
