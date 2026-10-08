<?php

namespace Tests\Feature;

use App\Models\Cuidado;
use App\Models\Idoso;
use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdosoTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create();
    }

    private function dadosValidos(array $alteracoes = []): array
    {
        return array_merge([
            'nome' => 'Maria das Graças Souza',
            'data_nascimento' => '1942-03-18',
            'sexo' => 'feminino',
            'cpf' => Cpf::formatar(Cpf::gerar()),
            'tipo_sanguineo' => 'O+',
            'telefone' => '(91) 3222-1040',
            'contato_emergencia_nome' => 'Raimunda (filha)',
            'contato_emergencia_telefone' => '(91) 98811-2040',
            'condicoes_saude' => 'Hipertensão arterial',
            'alergias' => 'Dipirona',
            'ativo' => '1',
        ], $alteracoes);
    }

    public function test_lista_idosos_ativos(): void
    {
        Idoso::factory()->create(['nome' => 'Maria das Graças Souza']);
        Idoso::factory()->inativo()->create(['nome' => 'Antônio Pereira']);

        $this->actingAs($this->usuario)
            ->get(route('idosos.index'))
            ->assertOk()
            ->assertSee('Maria das Graças Souza')
            ->assertDontSee('Antônio Pereira');

        $this->actingAs($this->usuario)
            ->get(route('idosos.index', ['situacao' => 'todos']))
            ->assertSee('Antônio Pereira');
    }

    public function test_busca_por_nome(): void
    {
        Idoso::factory()->create(['nome' => 'Maria das Graças Souza']);
        Idoso::factory()->create(['nome' => 'José Ribamar Costa']);

        $this->actingAs($this->usuario)
            ->get(route('idosos.index', ['q' => 'ribamar']))
            ->assertOk()
            ->assertSee('José Ribamar Costa')
            ->assertDontSee('Maria das Graças Souza');
    }

    public function test_cadastra_idoso(): void
    {
        $cpf = Cpf::gerar();

        $resposta = $this->actingAs($this->usuario)
            ->post(route('idosos.store'), $this->dadosValidos(['cpf' => Cpf::formatar($cpf)]));

        $idoso = Idoso::firstOrFail();

        $resposta->assertRedirect(route('idosos.show', $idoso));
        $this->assertSame($cpf, $idoso->cpf, 'O CPF deve ser guardado só com números.');
        $this->assertTrue($idoso->ativo);
        $this->assertSame('1942-03-18', $idoso->data_nascimento->format('Y-m-d'));
    }

    public function test_campos_obrigatorios_e_cpf_invalido(): void
    {
        $this->actingAs($this->usuario)
            ->from(route('idosos.create'))
            ->post(route('idosos.store'), $this->dadosValidos([
                'nome' => '',
                'data_nascimento' => now()->addDay()->format('Y-m-d'),
                'cpf' => '111.111.111-11',
            ]))
            ->assertRedirect(route('idosos.create'))
            ->assertSessionHasErrors(['nome', 'data_nascimento', 'cpf']);

        $this->assertDatabaseCount('idosos', 0);
    }

    public function test_nao_aceita_cpf_repetido(): void
    {
        $existente = Idoso::factory()->create();

        $this->actingAs($this->usuario)
            ->post(route('idosos.store'), $this->dadosValidos(['cpf' => $existente->cpf]))
            ->assertSessionHasErrors('cpf');
    }

    public function test_ficha_mostra_alergias_e_historico(): void
    {
        $idoso = Idoso::factory()->create(['alergias' => 'Penicilina']);
        Cuidado::factory()->for($idoso)->medicacao('Donepezila', '10 mg')->create();

        $this->actingAs($this->usuario)
            ->get(route('idosos.show', $idoso))
            ->assertOk()
            ->assertSee('Penicilina')
            ->assertSee('Donepezila');
    }

    public function test_atualiza_e_desativa(): void
    {
        $idoso = Idoso::factory()->create();

        $this->actingAs($this->usuario)
            ->put(route('idosos.update', $idoso), $this->dadosValidos([
                'nome' => 'Nome Corrigido',
                'cpf' => $idoso->cpf,
                'ativo' => '0',
            ]))
            ->assertRedirect(route('idosos.show', $idoso));

        $idoso->refresh();
        $this->assertSame('Nome Corrigido', $idoso->nome);
        $this->assertFalse($idoso->ativo);
    }

    public function test_exclui_idoso_e_seus_cuidados(): void
    {
        $idoso = Idoso::factory()->create();
        Cuidado::factory()->count(3)->for($idoso)->create();
        $outro = Cuidado::factory()->create();

        $this->actingAs($this->usuario)
            ->delete(route('idosos.destroy', $idoso))
            ->assertRedirect(route('idosos.index'));

        $this->assertModelMissing($idoso);
        $this->assertDatabaseCount('cuidados', 1);
        $this->assertModelExists($outro);
    }
}
