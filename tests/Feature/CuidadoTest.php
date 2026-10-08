<?php

namespace Tests\Feature;

use App\Enums\StatusCuidado;
use App\Enums\TipoCuidado;
use App\Models\Cuidado;
use App\Models\Idoso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuidadoTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Idoso $idoso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create(['name' => 'Ana Paula']);
        $this->idoso = Idoso::factory()->create(['nome' => 'Maria das Graças Souza']);
    }

    private function dados(array $alteracoes = []): array
    {
        return array_merge([
            'idoso_id' => $this->idoso->id,
            'tipo' => 'alimentacao',
            'status' => 'realizado',
            'data_hora' => now()->format('Y-m-d\TH:i'),
            'responsavel' => 'Ana Paula',
            'descricao' => 'Almoço: comeu tudo.',
            'medicamento' => '',
            'dosagem' => '',
            'pressao_sistolica' => '',
            'pressao_diastolica' => '',
            'frequencia_cardiaca' => '',
            'temperatura' => '',
            'glicemia' => '',
            'saturacao' => '',
            'observacoes' => '',
        ], $alteracoes);
    }

    public function test_formulario_ja_vem_com_idoso_hora_e_responsavel(): void
    {
        $this->actingAs($this->usuario)
            ->get(route('cuidados.create', ['idoso' => $this->idoso->id]))
            ->assertOk()
            ->assertSee('Maria das Graças Souza')
            ->assertSee('value="Ana Paula"', false)
            ->assertSee(now()->format('Y-m-d\TH:'), false);
    }

    public function test_registra_cuidado(): void
    {
        $this->actingAs($this->usuario)
            ->post(route('cuidados.store'), $this->dados())
            ->assertRedirect(route('idosos.show', $this->idoso));

        $cuidado = Cuidado::firstOrFail();
        $this->assertSame(TipoCuidado::Alimentacao, $cuidado->tipo);
        $this->assertSame(StatusCuidado::Realizado, $cuidado->status);
        $this->assertSame($this->usuario->id, $cuidado->user_id);
    }

    public function test_medicacao_exige_o_nome_do_medicamento(): void
    {
        $this->actingAs($this->usuario)
            ->post(route('cuidados.store'), $this->dados(['tipo' => 'medicacao']))
            ->assertSessionHasErrors('medicamento');

        $this->actingAs($this->usuario)
            ->post(route('cuidados.store'), $this->dados([
                'tipo' => 'medicacao',
                'medicamento' => 'Losartana',
                'dosagem' => '50 mg',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cuidados', ['medicamento' => 'Losartana', 'dosagem' => '50 mg']);
    }

    public function test_sinais_vitais_exigem_ao_menos_um_valor(): void
    {
        $this->actingAs($this->usuario)
            ->post(route('cuidados.store'), $this->dados(['tipo' => 'sinais_vitais']))
            ->assertSessionHasErrors('pressao_sistolica');
    }

    public function test_aceita_temperatura_com_virgula_e_destaca_valores_fora_da_faixa(): void
    {
        $this->actingAs($this->usuario)
            ->post(route('cuidados.store'), $this->dados([
                'tipo' => 'sinais_vitais',
                'descricao' => 'Aferição da manhã.',
                'pressao_sistolica' => '160',
                'pressao_diastolica' => '100',
                'temperatura' => '36,8',
            ]))
            ->assertSessionHasNoErrors();

        $cuidado = Cuidado::firstOrFail();
        $this->assertEquals(36.8, (float) $cuidado->temperatura);
        $this->assertCount(2, $cuidado->alertas());

        $this->actingAs($this->usuario)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Sinais fora da faixa')
            ->assertSee('Pressão máxima acima da faixa usual');
    }

    public function test_pressao_precisa_dos_dois_valores(): void
    {
        $this->actingAs($this->usuario)
            ->post(route('cuidados.store'), $this->dados([
                'tipo' => 'sinais_vitais',
                'pressao_sistolica' => '120',
            ]))
            ->assertSessionHasErrors('pressao_diastolica');
    }

    public function test_marca_pendente_como_feito(): void
    {
        $cuidado = Cuidado::factory()->for($this->idoso)->medicacao()->pendente()->create();

        $this->actingAs($this->usuario)
            ->from(route('dashboard'))
            ->patch(route('cuidados.concluir', $cuidado))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(StatusCuidado::Realizado, $cuidado->fresh()->status);
    }

    public function test_afericao_pendente_abre_o_formulario_para_preencher(): void
    {
        $cuidado = Cuidado::factory()->for($this->idoso)->pendente()->create([
            'tipo' => TipoCuidado::SinaisVitais,
        ]);

        $this->actingAs($this->usuario)
            ->patch(route('cuidados.concluir', $cuidado))
            ->assertRedirect(route('cuidados.edit', [$cuidado, 'concluir' => 1]));

        $this->assertSame(StatusCuidado::Pendente, $cuidado->fresh()->status);
    }

    public function test_repetir_copia_o_medicamento(): void
    {
        $origem = Cuidado::factory()->for($this->idoso)->medicacao('Sertralina', '50 mg')->create();

        $this->actingAs($this->usuario)
            ->get(route('cuidados.create', ['copiar' => $origem->id]))
            ->assertOk()
            ->assertSee('value="Sertralina"', false);
    }

    public function test_filtra_historico(): void
    {
        Cuidado::factory()->for($this->idoso)->medicacao('Losartana')->create();
        Cuidado::factory()->for($this->idoso)->create([
            'tipo' => TipoCuidado::Higiene,
            'descricao' => 'Banho no leito.',
        ]);

        $this->actingAs($this->usuario)
            ->get(route('cuidados.index', ['tipo' => 'medicacao']))
            ->assertOk()
            ->assertSee('Losartana')
            ->assertDontSee('Banho no leito.');

        $this->actingAs($this->usuario)
            ->get(route('cuidados.index', ['q' => 'leito', 'tipo' => 'invalido', 'de' => 'xx']))
            ->assertOk()
            ->assertSee('Banho no leito.');
    }

    public function test_detalhe_edicao_e_exclusao(): void
    {
        $cuidado = Cuidado::factory()->for($this->idoso)->create(['descricao' => 'Caminhada curta.']);

        $this->actingAs($this->usuario)
            ->get(route('cuidados.show', $cuidado))
            ->assertOk()
            ->assertSee('Caminhada curta.');

        $this->actingAs($this->usuario)
            ->get(route('cuidados.edit', $cuidado))
            ->assertOk();

        $this->actingAs($this->usuario)
            ->put(route('cuidados.update', $cuidado), $this->dados([
                'tipo' => 'mobilidade',
                'descricao' => 'Caminhada de 20 minutos.',
            ]))
            ->assertRedirect(route('cuidados.show', $cuidado));

        $this->assertSame('Caminhada de 20 minutos.', $cuidado->fresh()->descricao);

        $this->actingAs($this->usuario)
            ->delete(route('cuidados.destroy', $cuidado))
            ->assertRedirect(route('idosos.show', $this->idoso));

        $this->assertModelMissing($cuidado);
    }

    public function test_todas_as_telas_abrem_com_os_dados_de_exemplo(): void
    {
        // Os dados de exemplo só são criados com o banco sem idosos.
        Idoso::query()->delete();
        $this->seed();

        $admin = User::where('email', 'admin@exemplo.com')->firstOrFail();
        $idoso = Idoso::where('ativo', true)->firstOrFail();
        $cuidado = Cuidado::firstOrFail();

        $urls = [
            route('dashboard'),
            route('idosos.index'),
            route('idosos.index', ['situacao' => 'todos']),
            route('idosos.create'),
            route('idosos.show', $idoso),
            route('idosos.show', [$idoso, 'tipo' => 'medicacao']),
            route('idosos.edit', $idoso),
            route('cuidados.index'),
            route('cuidados.index', ['status' => 'pendente']),
            route('cuidados.create'),
            route('cuidados.show', $cuidado),
            route('cuidados.edit', $cuidado),
            route('usuarios.index'),
            route('usuarios.create'),
            route('usuarios.edit', $admin),
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
