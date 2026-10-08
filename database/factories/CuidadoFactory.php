<?php

namespace Database\Factories;

use App\Enums\StatusCuidado;
use App\Enums\TipoCuidado;
use App\Models\Cuidado;
use App\Models\Idoso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cuidado>
 */
class CuidadoFactory extends Factory
{
    protected $model = Cuidado::class;

    public function definition(): array
    {
        $tipo = fake()->randomElement([
            TipoCuidado::Alimentacao,
            TipoCuidado::Hidratacao,
            TipoCuidado::Higiene,
            TipoCuidado::Mobilidade,
            TipoCuidado::Sono,
        ]);

        return [
            'idoso_id' => Idoso::factory(),
            'tipo' => $tipo,
            'status' => StatusCuidado::Realizado,
            'data_hora' => fake()->dateTimeBetween('-7 days', 'now'),
            'responsavel' => fake()->firstName(),
            'descricao' => match ($tipo) {
                TipoCuidado::Alimentacao => 'Almoço: arroz, feijão, frango e legumes. Comeu quase tudo.',
                TipoCuidado::Hidratacao => 'Tomou 2 copos de água e 1 de suco.',
                TipoCuidado::Higiene => 'Banho de chuveiro com cadeira de banho.',
                TipoCuidado::Mobilidade => 'Caminhada de 15 minutos pelo corredor, com apoio.',
                default => 'Dormiu bem, acordou uma vez para ir ao banheiro.',
            },
        ];
    }

    public function medicacao(string $medicamento = 'Losartana', string $dosagem = '50 mg'): static
    {
        return $this->state(fn () => [
            'tipo' => TipoCuidado::Medicacao,
            'descricao' => "{$medicamento} {$dosagem} via oral, com água.",
            'medicamento' => $medicamento,
            'dosagem' => $dosagem,
        ]);
    }

    /** @param  array<string, int|float>  $valores */
    public function sinaisVitais(array $valores = []): static
    {
        return $this->state(fn () => array_merge([
            'tipo' => TipoCuidado::SinaisVitais,
            'descricao' => 'Aferição de rotina.',
            'pressao_sistolica' => 125,
            'pressao_diastolica' => 80,
            'frequencia_cardiaca' => 72,
            'temperatura' => 36.4,
            'glicemia' => 110,
            'saturacao' => 96,
        ], $valores));
    }

    public function pendente(): static
    {
        return $this->state(fn () => [
            'status' => StatusCuidado::Pendente,
            'data_hora' => now()->addHours(2),
        ]);
    }
}
