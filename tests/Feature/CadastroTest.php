<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CadastroTest extends TestCase
{
    use RefreshDatabase;

    private function dados(array $alteracoes = []): array
    {
        return array_merge([
            'name' => 'Ana Paula',
            'email' => 'ana@exemplo.com',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ], $alteracoes);
    }

    public function test_tela_de_cadastro_abre_com_link_para_entrar(): void
    {
        $this->get(route('cadastro'))
            ->assertOk()
            ->assertSee('Criar seu acesso')
            ->assertSee(route('login'));
    }

    public function test_tela_de_entrar_tem_link_para_cadastro(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('cadastro'));
    }

    public function test_cria_o_proprio_acesso_e_ja_entra(): void
    {
        $this->post(route('cadastro.store'), $this->dados())
            ->assertRedirect(route('dashboard'));

        $usuario = User::where('email', 'ana@exemplo.com')->firstOrFail();
        $this->assertSame('Ana Paula', $usuario->name);
        $this->assertTrue(Hash::check('senha-segura', $usuario->password));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_nao_aceita_email_ja_cadastrado(): void
    {
        User::factory()->create(['email' => 'ana@exemplo.com']);

        $this->from(route('cadastro'))
            ->post(route('cadastro.store'), $this->dados())
            ->assertRedirect(route('cadastro'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_senhas_precisam_ser_iguais_e_ter_8_caracteres(): void
    {
        $this->post(route('cadastro.store'), $this->dados(['password_confirmation' => 'outra-senha']))
            ->assertSessionHasErrors('password');

        $this->post(route('cadastro.store'), $this->dados(['password' => 'curta', 'password_confirmation' => 'curta']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_quem_ja_entrou_nao_ve_o_cadastro(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('cadastro'))
            ->assertRedirect();
    }

    public function test_login_continua_levando_para_o_inicio(): void
    {
        $usuario = User::factory()->create(['password' => bcrypt('senha-segura')]);

        $this->post(route('login.store'), ['email' => $usuario->email, 'password' => 'senha-segura'])
            ->assertRedirect(route('dashboard'));
    }
}
