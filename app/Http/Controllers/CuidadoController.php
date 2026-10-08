<?php

namespace App\Http\Controllers;

use App\Enums\StatusCuidado;
use App\Enums\TipoCuidado;
use App\Http\Requests\CuidadoRequest;
use App\Models\Cuidado;
use App\Models\Idoso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CuidadoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $this->filtros($request);

        $cuidados = Cuidado::with(['idoso', 'registradoPor'])
            ->filtrar($filtros)
            ->orderByDesc('data_hora')
            ->orderByDesc('id')
            ->paginate(config('cuidados.por_pagina'))
            ->withQueryString();

        return view('cuidados.index', [
            'cuidados' => $cuidados,
            'filtros' => $filtros,
            'temFiltro' => array_filter($filtros) !== [],
            'idosos' => Idoso::orderBy('nome')->get(['id', 'nome', 'ativo']),
        ]);
    }

    public function create(Request $request): View
    {
        $origem = $this->numero($request, 'copiar')
            ? Cuidado::find($this->numero($request, 'copiar'))
            : null;

        if ($origem) {
            // "Repetir": mesmo idoso, tipo, descrição e medicamento; horário de agora.
            $cuidado = new Cuidado($origem->only([
                'idoso_id', 'tipo', 'descricao', 'medicamento', 'dosagem',
            ]));
        } else {
            $cuidado = new Cuidado([
                'idoso_id' => Idoso::whereKey($this->numero($request, 'idoso'))->value('id'),
                'tipo' => is_string($request->query('tipo')) ? TipoCuidado::tryFrom($request->query('tipo')) : null,
            ]);
        }

        $cuidado->status = StatusCuidado::Realizado;
        $cuidado->data_hora = now()->startOfMinute();
        $cuidado->responsavel = $request->user()->name;

        return view('cuidados.create', [
            'cuidado' => $cuidado,
            'idosos' => $this->idososParaFormulario($cuidado),
            'voltar' => $request->query('voltar') === 'painel' ? 'painel' : null,
        ]);
    }

    public function store(CuidadoRequest $request): RedirectResponse
    {
        $cuidado = new Cuidado($request->dados());
        $cuidado->user_id = $request->user()->id;
        $cuidado->save();

        $mensagem = $cuidado->status === StatusCuidado::Pendente ? 'Cuidado agendado.' : 'Cuidado registrado.';

        if ($request->input('voltar') === 'painel') {
            return redirect()->route('dashboard')->with('sucesso', $mensagem);
        }

        return redirect()
            ->route('idosos.show', $cuidado->idoso_id)
            ->with('sucesso', $mensagem);
    }

    public function show(Cuidado $cuidado): View
    {
        $cuidado->load(['idoso', 'registradoPor']);

        return view('cuidados.show', ['cuidado' => $cuidado]);
    }

    public function edit(Request $request, Cuidado $cuidado): View
    {
        // Vindo do botão "Marcar como feito" de uma aferição pendente:
        // já deixa o status como feito para só preencher os valores.
        if ($request->boolean('concluir')) {
            $cuidado->status = StatusCuidado::Realizado;
        }

        return view('cuidados.edit', [
            'cuidado' => $cuidado,
            'idosos' => $this->idososParaFormulario($cuidado),
        ]);
    }

    public function update(CuidadoRequest $request, Cuidado $cuidado): RedirectResponse
    {
        $cuidado->update($request->dados());

        return redirect()
            ->route('cuidados.show', $cuidado)
            ->with('sucesso', 'Registro atualizado.');
    }

    public function destroy(Cuidado $cuidado): RedirectResponse
    {
        $cuidado->delete();

        return redirect()
            ->route('idosos.show', $cuidado->idoso_id)
            ->with('sucesso', 'Registro excluído.');
    }

    /** Marca um cuidado pendente como feito. */
    public function concluir(Cuidado $cuidado): RedirectResponse
    {
        if ($cuidado->status !== StatusCuidado::Pendente) {
            return back()->with('sucesso', 'Este cuidado já estava registrado.');
        }

        // Aferição precisa dos valores: abre o formulário para preencher.
        if ($cuidado->tipo === TipoCuidado::SinaisVitais) {
            return redirect()->route('cuidados.edit', [$cuidado, 'concluir' => 1]);
        }

        $cuidado->update(['status' => StatusCuidado::Realizado]);

        return back()->with('sucesso', 'Marcado como feito.');
    }

    /**
     * Lê e limpa os filtros da listagem (valores inválidos são ignorados).
     *
     * @return array{idoso: ?int, tipo: ?string, status: ?string, de: ?string, ate: ?string, q: ?string}
     */
    private function filtros(Request $request): array
    {
        $texto = fn (string $chave): string => is_string($request->query($chave)) ? trim($request->query($chave)) : '';
        $data = fn (string $chave): ?string => preg_match('/^\d{4}-\d{2}-\d{2}$/', $texto($chave)) ? $texto($chave) : null;
        $busca = mb_substr($texto('q'), 0, 100);

        return [
            'idoso' => $this->numero($request, 'idoso'),
            'tipo' => TipoCuidado::tryFrom($texto('tipo'))?->value,
            'status' => StatusCuidado::tryFrom($texto('status'))?->value,
            'de' => $data('de'),
            'ate' => $data('ate'),
            'q' => $busca === '' ? null : $busca,
        ];
    }

    private function numero(Request $request, string $chave): ?int
    {
        $valor = $request->query($chave);

        return is_string($valor) && ctype_digit($valor) ? (int) $valor : null;
    }

    /** Idosos ativos, mais o idoso do registro (caso ele esteja inativo). */
    private function idososParaFormulario(Cuidado $cuidado)
    {
        return Idoso::query()
            ->where('ativo', true)
            ->when($cuidado->idoso_id, fn ($q) => $q->orWhere('id', $cuidado->idoso_id))
            ->orderBy('nome')
            ->get(['id', 'nome', 'data_nascimento']);
    }
}
