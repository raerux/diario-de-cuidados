@extends('layouts.app')

@section('titulo', 'Criar acesso')

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <a class="voltar" href="{{ route('usuarios.index') }}">Voltar para os acessos</a>
            <h1>Criar acesso</h1>
            <p class="subtitulo">Depois de criado, passe o e-mail e a senha para a pessoa. Ela poderá registrar cuidados e editar cadastros.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('usuarios.store') }}" class="formulario" novalidate>
        @csrf
        @include('usuarios._form')

        <div class="rodape-formulario">
            <button type="submit" class="botao botao-principal">Criar acesso</button>
            <a href="{{ route('usuarios.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
