<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_disabled')->default(false)->after('is_platform_admin')->index();
        });

        Schema::create('platform_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('impersonator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->string('action', 64);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('summary');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['empresa_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug', 64)->unique();
            $table->boolean('ativo')->default(true)->index();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->unsignedSmallInteger('max_users')->default(5);
            $table->unsignedInteger('max_storage_mb')->default(1024);
            $table->timestamps();
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('logo_path')->constrained('plans')->nullOnDelete();
            $table->json('plan_overrides')->nullable()->after('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn('plan_overrides');
        });

        Schema::dropIfExists('plans');
        Schema::dropIfExists('platform_activity_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_disabled');
        });
    }
};

