<?php

namespace Database\Factories;

use App\Enums\Sexo;
use App\Models\Idoso;
use App\Rules\Cpf;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Idoso>
 */
class IdosoFactory extends Factory
{
    protected $model = Idoso::class;

    public function definition(): array
    {
        $sexo = fake()->randomElement([Sexo::Feminino, Sexo::Masculino]);

        return [
            'nome' => fake()->name($sexo === Sexo::Feminino ? 'female' : 'male'),
            'data_nascimento' => fake()->dateTimeBetween('-98 years', '-62 years')->format('Y-m-d'),
            'sexo' => $sexo,
            'cpf' => Cpf::gerar(),
            'tipo_sanguineo' => fake()->randomElement(Idoso::TIPOS_SANGUINEOS),
            'telefone' => fake()->numerify('(##) 9####-####'),
            'endereco' => fake()->streetAddress(),
            'contato_emergencia_nome' => fake()->name(),
            'contato_emergencia_telefone' => fake()->numerify('(##) 9####-####'),
            'condicoes_saude' => fake()->randomElement([null, 'Hipertensão arterial', 'Diabetes tipo 2', 'Artrose nos joelhos']),
            'alergias' => fake()->randomElement([null, null, 'Dipirona', 'Frutos do mar']),
            'medicamentos_continuos' => null,
            'observacoes' => null,
            'ativo' => true,
        ];
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['ativo' => false]);
    }
}
