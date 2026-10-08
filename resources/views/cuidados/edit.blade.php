@extends('layouts.app')

@section('titulo', 'Editar registro')

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <a class="voltar" href="{{ route('cuidados.show', $cuidado) }}">Voltar para o registro</a>
            <h1>Editar registro</h1>
            <p class="subtitulo">{{ $cuidado->tipo->label() }} de {{ $cuidado->idoso->nome }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('cuidados.update', $cuidado) }}" class="formulario" novalidate>
        @csrf
        @method('PUT')
        @include('cuidados._form', ['voltar' => null])

        <div class="rodape-formulario">
            <button type="submit" class="botao botao-principal">Salvar alterações</button>
            <a href="{{ route('cuidados.show', $cuidado) }}">Cancelar</a>
        </div>
    </form>
@endsection
