<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('email_contato')->nullable()->after('cnpj');
            $table->string('telefone', 32)->nullable()->after('email_contato');
            $table->string('logo_path')->nullable()->after('telefone');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('summary');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['empresa_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });

        $rows = [
            ['slug' => 'roles.manage', 'name' => 'Gerir papéis e permissões'],
            ['slug' => 'cadastros.manage', 'name' => 'Gerir cadastros mestres (processos e documentos)'],
            ['slug' => 'empresa.manage', 'name' => 'Gerir dados da empresa'],
            ['slug' => 'auditoria.view', 'name' => 'Ver auditoria do sistema'],
            ['slug' => 'relatorios.view', 'name' => 'Ver relatórios'],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('permissions')->where('slug', $row['slug'])->exists();
            if (! $exists) {
                DB::table('permissions')->insert([
                    'slug' => $row['slug'],
                    'name' => $row['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $permIds = DB::table('permissions')
            ->whereIn('slug', array_column($rows, 'slug'))
            ->pluck('id');

        $adminRoleIds = DB::table('roles')->where('slug', 'administrador')->pluck('id');

        foreach ($adminRoleIds as $roleId) {
            foreach ($permIds as $pid) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $pid,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['email_contato', 'telefone', 'logo_path']);
        });

        $slugs = ['roles.manage', 'cadastros.manage', 'empresa.manage', 'auditoria.view', 'relatorios.view'];
        $ids = DB::table('permissions')->whereIn('slug', $slugs)->pluck('id');
        if ($ids->isNotEmpty()) {
            DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
            DB::table('permissions')->whereIn('id', $ids)->delete();
        }
    }
};
