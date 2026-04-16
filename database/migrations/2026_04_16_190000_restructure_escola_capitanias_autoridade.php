<?php

use App\Models\Habilitacao;
use App\Support\EscolaAutoridadeMaritima;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('escola_capitanias', function (Blueprint $table) {
            $table->string('capitania_jurisdicao', 255)->nullable()->after('escola_nautica_id');
            $table->text('capitania_endereco')->nullable()->after('capitania_jurisdicao');
            $table->string('representante_funcao', 120)->nullable()->after('capitania_endereco');
            $table->string('representante_posto', 120)->nullable()->after('representante_funcao');
            $table->string('representante_nome', 255)->nullable()->after('representante_posto');
            $table->string('representante_jurisdicao', 255)->nullable()->after('representante_nome');
            $table->text('representante_endereco')->nullable()->after('representante_jurisdicao');
        });

        $funcoes = EscolaAutoridadeMaritima::FUNCOES;
        $postos = EscolaAutoridadeMaritima::POSTOS;
        $juris = Habilitacao::JURISDICOES;

        $rows = DB::table('escola_capitanias')->get();
        foreach ($rows as $row) {
            $orgao = isset($row->orgao_jurisdicao) ? (string) $row->orgao_jurisdicao : '';
            $capJur = in_array($orgao, $juris, true) ? $orgao : null;

            $repFuncao = isset($row->funcao) && in_array((string) $row->funcao, $funcoes, true)
                ? (string) $row->funcao
                : null;
            $repPosto = isset($row->posto) && in_array((string) $row->posto, $postos, true)
                ? (string) $row->posto
                : null;

            DB::table('escola_capitanias')->where('id', $row->id)->update([
                'capitania_jurisdicao' => $capJur,
                'capitania_endereco' => $row->endereco ?? null,
                'representante_funcao' => $repFuncao,
                'representante_posto' => $repPosto,
                'representante_nome' => $row->nome_capitao_portos ?? null,
                'representante_jurisdicao' => $capJur,
                'representante_endereco' => $row->endereco ?? null,
            ]);
        }

        Schema::table('escola_capitanias', function (Blueprint $table) {
            $table->dropColumn([
                'funcao',
                'posto',
                'nome_capitao_portos',
                'orgao_jurisdicao',
                'endereco',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('escola_capitanias', function (Blueprint $table) {
            $table->string('funcao', 120)->nullable();
            $table->string('posto', 255)->nullable();
            $table->string('nome_capitao_portos', 255)->nullable();
            $table->string('orgao_jurisdicao', 255)->nullable();
            $table->text('endereco')->nullable();
        });

        $rows = DB::table('escola_capitanias')->get();
        foreach ($rows as $row) {
            DB::table('escola_capitanias')->where('id', $row->id)->update([
                'funcao' => $row->representante_funcao,
                'posto' => $row->representante_posto,
                'nome_capitao_portos' => $row->representante_nome,
                'orgao_jurisdicao' => $row->capitania_jurisdicao ?? $row->representante_jurisdicao,
                'endereco' => $row->capitania_endereco ?? $row->representante_endereco,
            ]);
        }

        Schema::table('escola_capitanias', function (Blueprint $table) {
            $table->dropColumn([
                'capitania_jurisdicao',
                'capitania_endereco',
                'representante_funcao',
                'representante_posto',
                'representante_nome',
                'representante_jurisdicao',
                'representante_endereco',
            ]);
        });
    }
};
