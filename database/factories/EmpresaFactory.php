<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Support\BrazilStates;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        $nome = fake()->company();

        return [
            'nome' => $nome,
            'slug' => Str::slug($nome).'-'.fake()->unique()->numerify('###'),
            'cnpj' => null,
            'uf' => fake()->randomElement(BrazilStates::codes()),
            'ativo' => true,
        ];
    }
}
