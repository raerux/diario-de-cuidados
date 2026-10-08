<?php

namespace App\Http\Controllers;

use App\Enums\StatusCuidado;
use App\Enums\TipoCuidado;
use App\Http\Requests\IdosoRequest;
use App\Models\Idoso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IdosoController extends Controller
{
    public function index(Request $request): View
    {
        $busca = is_string($request->query('q')) ? mb_substr(trim($request->query('q')), 0, 100) : '';
        $situacao = in_array($request->query('situacao'), ['ativos', 'inativos', 'todos'], true)
            ? $request->query('situacao')
            : 'ativos';

        $idosos = Idoso::query()
            ->busca($busca)
            ->when($situacao === 'ativos', fn ($q) => $q->where('ativo', true))
            ->when($situacao === 'inativos', fn ($q) => $q->where('ativo', false))
            ->withCount([
                'cuidados as pendentes_count' => fn ($q) => $q->where('status', StatusCuidado::Pendente->value),
            ])
            ->withMax(['cuidados as ultimo_cuidado_em' => fn ($q) => $q->where('status', StatusCuidado::Realizado->value)], 'data_hora')
            ->withCasts(['ultimo_cuidado_em' => 'datetime'])
            ->orderBy('nome')
            ->paginate(config('cuidados.por_pagina'))
            ->withQueryString();

        return view('idosos.index', [
            'idosos' => $idosos,
            'situacao' => $situacao,
            'q' => $busca,
        ]);
    }

    public function create(): View
    {
        return view('idosos.create', ['idoso' => new Idoso]);
    }

    public function store(IdosoRequest $request): RedirectResponse
    {
        $idoso = Idoso::create($request->validated());

        return redirect()
            ->route('idosos.show', $idoso)
            ->with('sucesso', "{$idoso->nome} foi cadastrado(a).");
    }

    public function show(Request $request, Idoso $idoso): View
    {
        $tipo = is_string($request->query('tipo')) ? TipoCuidado::tryFrom($request->query('tipo')) : null;

        // Histórico: o que foi feito (ou deixou de ser). Os pendentes aparecem à parte.
        $cuidados = $idoso->cuidados()
            ->where('status', '!=', StatusCuidado::Pendente->value)
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo->value))
            ->orderByDesc('data_hora')
            ->orderByDesc('id')
            ->paginate(config('cuidados.por_pagina'))
            ->withQueryString();

        $ultimosSinais = $idoso->cuidados()
            ->where('tipo', TipoCuidado::SinaisVitais->value)
            ->where('status', StatusCuidado::Realizado->value)
            ->orderByDesc('data_hora')
            ->first();

        $pendentes = $idoso->cuidados()
            ->pendentes()
            ->orderBy('data_hora')
            ->get();

        return view('idosos.show', [
            'idoso' => $idoso,
            'cuidados' => $cuidados,
            'tipoFiltro' => $tipo,
            'ultimosSinais' => $ultimosSinais,
            'pendentes' => $pendentes,
        ]);
    }

    public function edit(Idoso $idoso): View
    {
        return view('idosos.edit', ['idoso' => $idoso]);
    }

    public function update(IdosoRequest $request, Idoso $idoso): RedirectResponse
    {
        $idoso->update($request->validated());

        return redirect()
            ->route('idosos.show', $idoso)
            ->with('sucesso', 'Cadastro atualizado.');
    }

    public function destroy(Idoso $idoso): RedirectResponse
    {
        DB::transaction(function () use ($idoso) {
            $idoso->cuidados()->delete();
            $idoso->delete();
        });

        return redirect()
            ->route('idosos.index')
            ->with('sucesso', "{$idoso->nome} e todo o histórico de cuidados foram excluídos.");
    }
}
