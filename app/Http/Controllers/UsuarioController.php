<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        return view('usuarios.index', [
            'usuarios' => User::orderBy('name')->paginate(config('cuidados.por_pagina')),
        ]);
    }

    public function create(): View
    {
        return view('usuarios.create', ['usuario' => new User]);
    }

    public function store(UsuarioRequest $request): RedirectResponse
    {
        $usuario = new User;
        $usuario->forceFill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ])->save();

        return redirect()
            ->route('usuarios.index')
            ->with('sucesso', "Acesso criado para {$usuario->name}.");
    }

    public function edit(User $usuario): View
    {
        return view('usuarios.edit', ['usuario' => $usuario]);
    }

    public function update(UsuarioRequest $request, User $usuario): RedirectResponse
    {
        $usuario->forceFill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ]);

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->validated('password'));
        }

        $usuario->save();

        return redirect()
            ->route('usuarios.index')
            ->with('sucesso', 'Acesso atualizado.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('erro', 'Você não pode excluir o próprio acesso.');
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('sucesso', "O acesso de {$usuario->name} foi removido. Os registros feitos por essa pessoa continuam no histórico.");
    }
}
