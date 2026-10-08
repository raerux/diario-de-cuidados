@extends('layouts.app')

@section('titulo', 'Cadastrar idoso')

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <a class="voltar" href="{{ route('idosos.index') }}">Voltar para a lista de idosos</a>
            <h1>Cadastrar idoso</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('idosos.store') }}" class="formulario" novalidate>
        @csrf
        @include('idosos._form')

        <div class="rodape-formulario">
            <button type="submit" class="botao botao-principal">Cadastrar</button>
            <a href="{{ route('idosos.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
