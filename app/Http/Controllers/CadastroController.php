<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/** Tela inicial para quem chega sem estar conectado: a pessoa cria o próprio acesso. */
class CadastroController extends Controller
{
    public function create(): View
    {
        return view('auth.cadastro', ['usuario' => new User]);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        $usuario = new User;
        $usuario->forceFill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ])->save();

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('sucesso', "Bem-vindo(a), {$usuario->name}! Seu acesso foi criado.");
    }
}
