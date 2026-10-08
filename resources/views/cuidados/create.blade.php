@extends('layouts.app')

@section('titulo', 'Registrar cuidado')

@php
    $idosoEscolhido = $cuidado->idoso_id ? $idosos->firstWhere('id', $cuidado->idoso_id) : null;
    $urlVoltar = $voltar === 'painel' || ! $idosoEscolhido
        ? route('dashboard')
        : route('idosos.show', $idosoEscolhido);
@endphp

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <a class="voltar" href="{{ $urlVoltar }}">Voltar</a>
            <h1>Registrar cuidado</h1>
            @if ($idosoEscolhido)
                <p class="subtitulo">{{ $idosoEscolhido->nome }}</p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('cuidados.store') }}" class="formulario" novalidate>
        @csrf
        @include('cuidados._form')

        <div class="rodape-formulario">
            <button type="submit" class="botao botao-principal">Salvar registro</button>
            <a href="{{ $urlVoltar }}">Cancelar</a>
        </div>
    </form>
@endsection
