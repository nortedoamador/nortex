<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_anexo_tipos')) {
            return;
        }

        Schema::table('platform_anexo_tipos', function (Blueprint $table) {
            if (Schema::hasColumn('platform_anexo_tipos', 'contexto_uso')) {
                $table->dropColumn('contexto_uso');
            }
        });

        Schema::table('platform_anexo_tipos', function (Blueprint $table) {
            if (! Schema::hasColumn('platform_anexo_tipos', 'contexto_modulos')) {
                $table->json('contexto_modulos')->nullable()->after('is_multiple');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('platform_anexo_tipos')) {
            return;
        }

        Schema::table('platform_anexo_tipos', function (Blueprint $table) {
            if (Schema::hasColumn('platform_anexo_tipos', 'contexto_modulos')) {
                $table->dropColumn('contexto_modulos');
            }
        });

        Schema::table('platform_anexo_tipos', function (Blueprint $table) {
            if (! Schema::hasColumn('platform_anexo_tipos', 'contexto_uso')) {
                $table->text('contexto_uso')->nullable()->after('is_multiple');
            }
        });
    }
};
