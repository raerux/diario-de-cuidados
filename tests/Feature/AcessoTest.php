<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcessoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_levado_para_a_tela_de_entrar(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('idosos.index'))->assertRedirect(route('login'));
    }

    public function test_tela_de_entrar_aparece(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Entrar');
    }

    public function test_entra_com_email_e_senha_corretos(): void
    {
        $usuario = User::factory()->create(['password' => bcrypt('senha-segura')]);

        $this->post(route('login.store'), [
            'email' => $usuario->email,
            'password' => 'senha-segura',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_senha_errada_nao_entra(): void
    {
        $usuario = User::factory()->create(['password' => bcrypt('senha-segura')]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $usuario->email,
                'password' => 'outra-senha',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_sair(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_cria_edita_e_remove_acesso(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('usuarios.store'), [
                'name' => 'Ana Paula',
                'email' => 'ana@exemplo.com',
                'password' => 'cuidadora123',
                'password_confirmation' => 'cuidadora123',
            ])
            ->assertRedirect(route('usuarios.index'));

        $ana = User::where('email', 'ana@exemplo.com')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('usuarios.update', $ana), [
                'name' => 'Ana Paula Silva',
                'email' => 'ana@exemplo.com',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('usuarios.index'));

        $this->assertSame('Ana Paula Silva', $ana->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('usuarios.destroy', $ana))
            ->assertRedirect(route('usuarios.index'));

        $this->assertModelMissing($ana);
    }

    public function test_nao_remove_o_proprio_acesso(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->from(route('usuarios.edit', $admin))
            ->delete(route('usuarios.destroy', $admin))
            ->assertSessionHas('erro');

        $this->assertModelExists($admin);
    }
}
