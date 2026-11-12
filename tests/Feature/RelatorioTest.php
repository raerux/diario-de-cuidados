<?php

namespace Tests\Feature;

use App\Enums\StatusCuidado;
use App\Models\Cuidado;
use App\Models\Idoso;
use App\Models\User;
use App\Support\RelatorioSaude;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Idoso $idoso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create(['name' => 'Ana Paula']);
        $this->idoso = Idoso::factory()->create([
            'nome' => 'Maria das Graças Souza',
            'alergias' => 'Dipirona',
        ]);
    }

    public function test_exige_login(): void
    {
        $this->get(route('idosos.relatorio', $this->idoso))->assertRedirect(route('login'));
    }

    public function test_relatorio_vazio_abre_sem_erros(): void
    {
        $this->actingAs($this->usuario)
            ->get(route('idosos.relatorio', $this->idoso))
            ->assertOk()
            ->assertSee('Maria das Graças Souza')
            ->assertSee('Dipirona')
            ->assertSee('Nenhuma medição de sinais vitais registrada no período.')
            ->assertSee('Nada fora do comum foi registrado no período.');
    }

    public function test_mostra_grafico_e_destaca_sinal_fora_da_faixa(): void
    {
        Cuidado::factory()->for($this->idoso)->sinaisVitais(['pressao_sistolica' => 128])->create(['data_hora' => now()->subDays(2)]);
        Cuidado::factory()->for($this->idoso)->sinaisVitais(['pressao_sistolica' => 162])->create(['data_hora' => now()->subDay()]);

        $this->actingAs($this->usuario)
            ->get(route('idosos.relatorio', $this->idoso))
            ->assertOk()
            ->assertSee('<svg class="grafico"', false)
            ->assertSee('Evolução dos sinais vitais')
            ->assertSee('Pressão arterial: 1 medição fora da faixa usual, de 2 no período')
            ->assertSee('Pressão máxima acima da faixa usual (162 mmHg)');
    }

    public function test_calcula_doses_dadas_e_mostra_falhas(): void
    {
        foreach ([3, 2, 1] as $dias) {
            Cuidado::factory()->for($this->idoso)->medicacao('Losartana', '50 mg')->create(['data_hora' => now()->subDays($dias)]);
        }
        Cuidado::factory()->for($this->idoso)->medicacao('Losartana', '50 mg')->create([
            'data_hora' => now()->subDays(4),
            'status' => StatusCuidado::NaoRealizado,
            'observacoes' => 'Recusou, estava enjoada.',
        ]);
        // Pendente no futuro não conta como falha.
        Cuidado::factory()->for($this->idoso)->medicacao('Losartana', '50 mg')->pendente()->create();

        $relatorio = new RelatorioSaude($this->idoso, 14);
        $remedio = $relatorio->medicacao()[0];

        $this->assertSame(3, $remedio['dadas']);
        $this->assertSame(1, $remedio['nao_dadas']);
        $this->assertSame(0, $remedio['esquecidas']);
        $this->assertSame(75, $remedio['adesao']);
        $this->assertSame(75, $relatorio->adesaoGeral());

        $this->actingAs($this->usuario)
            ->get(route('idosos.relatorio', $this->idoso))
            ->assertOk()
            ->assertSee('Losartana: 1 dose não foi dada (75% das doses registradas foram dadas).')
            ->assertSee('Recusou, estava enjoada.');
    }

    public function test_pendente_atrasado_conta_como_dose_sem_registro(): void
    {
        Cuidado::factory()->for($this->idoso)->medicacao('Sertralina')->create(['data_hora' => now()->subDays(2)]);
        Cuidado::factory()->for($this->idoso)->medicacao('Sertralina')->create([
            'data_hora' => now()->subDay(),
            'status' => StatusCuidado::Pendente,
        ]);

        $remedio = (new RelatorioSaude($this->idoso, 7))->medicacao()[0];

        $this->assertSame(1, $remedio['esquecidas']);
        $this->assertSame(50, $remedio['adesao']);
    }

    public function test_respeita_o_periodo_escolhido(): void
    {
        Cuidado::factory()->for($this->idoso)->create([
            'data_hora' => now()->subDays(10),
            'observacoes' => 'Registro antigo de dez dias',
        ]);

        $this->actingAs($this->usuario)
            ->get(route('idosos.relatorio', [$this->idoso, 'dias' => 7]))
            ->assertOk()
            ->assertDontSee('Registro antigo de dez dias');

        $this->actingAs($this->usuario)
            ->get(route('idosos.relatorio', [$this->idoso, 'dias' => 14]))
            ->assertSee('Registro antigo de dez dias');
    }

    public function test_periodo_invalido_usa_o_padrao(): void
    {
        $this->actingAs($this->usuario)
            ->get(route('idosos.relatorio', [$this->idoso, 'dias' => 9999]))
            ->assertOk()
            ->assertSee('Últimos 14 dias');
    }

    public function test_nao_mistura_registros_de_outro_idoso(): void
    {
        $outro = Idoso::factory()->create();
        Cuidado::factory()->for($outro)->create(['observacoes' => 'Observação de outra pessoa']);

        $this->actingAs($this->usuario)
            ->get(route('idosos.relatorio', $this->idoso))
            ->assertDontSee('Observação de outra pessoa');
    }

    public function test_texto_do_usuario_no_grafico_e_escapado(): void
    {
        Cuidado::factory()->for($this->idoso)->sinaisVitais()->create([
            'observacoes' => '<script>alert(1)</script>',
            'temperatura' => 38.4,
        ]);

        $this->actingAs($this->usuario)
            ->get(route('idosos.relatorio', $this->idoso))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_ficha_tem_link_para_o_relatorio(): void
    {
        $this->actingAs($this->usuario)
            ->get(route('idosos.show', $this->idoso))
            ->assertSee(route('idosos.relatorio', $this->idoso));
    }
}
