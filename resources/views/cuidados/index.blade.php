@extends('layouts.app')

@section('titulo', 'Histórico de cuidados')

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <h1>Histórico de cuidados</h1>
            <p class="subtitulo">Todos os registros, do mais recente para o mais antigo. Use os filtros para encontrar um período, uma pessoa ou um tipo de cuidado.</p>
        </div>
        <div class="acoes">
            <a class="botao botao-principal" href="{{ route('cuidados.create', array_filter(['idoso' => $filtros['idoso'], 'tipo' => $filtros['tipo']])) }}">Registrar cuidado</a>
        </div>
    </div>

    <form method="GET" action="{{ route('cuidados.index') }}" class="filtros" role="search">
        <div class="campo">
            <label for="filtro-idoso">Idoso</label>
            <select id="filtro-idoso" name="idoso">
                <option value="">Todos</option>
                @foreach ($idosos as $idoso)
                    <option value="{{ $idoso->id }}" @selected($filtros['idoso'] === $idoso->id)>
                        {{ $idoso->nome }}{{ $idoso->ativo ? '' : ' (fora de acompanhamento)' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="campo">
            <label for="filtro-tipo">Tipo</label>
            <select id="filtro-tipo" name="tipo">
                <option value="">Todos</option>
                @foreach (\App\Enums\TipoCuidado::opcoes() as $valor => $rotulo)
                    <option value="{{ $valor }}" @selected($filtros['tipo'] === $valor)>{{ $rotulo }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo">
            <label for="filtro-status">Situação</label>
            <select id="filtro-status" name="status">
                <option value="">Todas</option>
                @foreach (\App\Enums\StatusCuidado::opcoes() as $valor => $rotulo)
                    <option value="{{ $valor }}" @selected($filtros['status'] === $valor)>{{ $rotulo }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo">
            <label for="filtro-de">De</label>
            <input type="date" id="filtro-de" name="de" value="{{ $filtros['de'] }}">
        </div>
        <div class="campo">
            <label for="filtro-ate">Até</label>
            <input type="date" id="filtro-ate" name="ate" value="{{ $filtros['ate'] }}">
        </div>
        <div class="campo">
            <label for="filtro-q">Palavra</label>
            <input type="search" id="filtro-q" name="q" value="{{ $filtros['q'] }}" placeholder="Remédio, nome…">
        </div>
        <div class="filtros-acoes">
            <button type="submit" class="botao">Filtrar</button>
            @if ($temFiltro)
                <a href="{{ route('cuidados.index') }}">Limpar</a>
            @endif
        </div>
    </form>

    @if ($temFiltro)
        <p class="resultado-filtro">
            {{ $cuidados->total() === 1 ? '1 registro encontrado.' : $cuidados->total().' registros encontrados.' }}
        </p>
    @endif

    @if ($cuidados->isEmpty())
        <div class="vazio">
            @if ($temFiltro)
                <p>Nenhum registro com esses filtros. Tente um período maior ou limpe os filtros.</p>
            @else
                <p>Nenhum cuidado registrado ainda.</p>
                <p><a class="botao botao-principal" href="{{ route('cuidados.create') }}">Registrar cuidado</a></p>
            @endif
        </div>
    @else
        @foreach ($cuidados->groupBy(fn ($c) => $c->data_hora->toDateString()) as $doDia)
            <section class="dia">
                <h2 class="dia-titulo">{{ \App\Support\Datas::rotuloDia($doDia->first()->data_hora) }}</h2>
                <ul class="diario">
                    @foreach ($doDia as $cuidado)
                        <li><x-registro :cuidado="$cuidado" :mostrar-idoso="! $filtros['idoso']" :acoes="$cuidado->idoso->ativo" /></li>
                    @endforeach
                </ul>
            </section>
        @endforeach

        {{ $cuidados->links() }}
    @endif
@endsection
