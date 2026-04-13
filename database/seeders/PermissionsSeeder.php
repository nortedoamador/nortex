<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['slug' => 'dashboard.view', 'name' => 'Ver dashboard'],
            ['slug' => 'processos.view', 'name' => 'Ver processos'],
            ['slug' => 'processos.create', 'name' => 'Criar processos'],
            ['slug' => 'processos.edit', 'name' => 'Editar processos'],
            ['slug' => 'processos.alterar_status', 'name' => 'Alterar status do processo'],
            ['slug' => 'processos.consulta_propria', 'name' => 'Consultar processos próprios'],
            ['slug' => 'clientes.view', 'name' => 'Ver clientes'],
            ['slug' => 'clientes.manage', 'name' => 'Gerir clientes'],
            ['slug' => 'embarcacoes.view', 'name' => 'Ver embarcações'],
            ['slug' => 'embarcacoes.manage', 'name' => 'Gerir embarcações'],
            ['slug' => 'habilitacoes.view', 'name' => 'Ver habilitações (CHA)'],
            ['slug' => 'habilitacoes.manage', 'name' => 'Gerir habilitações (CHA)'],
            ['slug' => 'financeiro.view', 'name' => 'Ver financeiro'],
            ['slug' => 'financeiro.manage', 'name' => 'Gerir financeiro'],
            ['slug' => 'aulas.view', 'name' => 'Ver aulas'],
            ['slug' => 'aulas.manage', 'name' => 'Gerir aulas'],
            ['slug' => 'usuarios.manage', 'name' => 'Gerir usuários e papéis'],
            ['slug' => 'roles.manage', 'name' => 'Gerir papéis e permissões'],
            ['slug' => 'cadastros.manage', 'name' => 'Gerir cadastros mestres (processos e documentos)'],
            ['slug' => 'empresa.manage', 'name' => 'Gerir dados da empresa'],
            ['slug' => 'auditoria.view', 'name' => 'Ver auditoria do sistema'],
            ['slug' => 'relatorios.view', 'name' => 'Ver relatórios'],
        ];

        foreach ($rows as $row) {
            Permission::query()->firstOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']],
            );
        }
    }
}
