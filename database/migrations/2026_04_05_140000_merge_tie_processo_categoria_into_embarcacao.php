<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tipo_processos')
            ->where('categoria', 'tie')
            ->update(['categoria' => 'embarcacao']);
    }

    public function down(): void
    {
        // Não reverte: não há como distinguir tipos que eram «tie» dos que já eram «embarcacao».
    }
};
