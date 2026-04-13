<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $ids = Permission::query()
            ->whereIn('slug', ['habilitacoes.view', 'habilitacoes.manage'])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        Role::query()
            ->whereIn('slug', ['administrador', 'operador'])
            ->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($ids->all()));
    }

    public function down(): void
    {
        //
    }
};
