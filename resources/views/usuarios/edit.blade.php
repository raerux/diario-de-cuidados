@extends('layouts.app')

@section('titulo', 'Editar acesso')

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <a class="voltar" href="{{ route('usuarios.index') }}">Voltar para os acessos</a>
            <h1>Editar acesso</h1>
            <p class="subtitulo">{{ $usuario->name }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="formulario" novalidate>
        @csrf
        @method('PUT')
        @include('usuarios._form')

        <div class="rodape-formulario">
            <button type="submit" class="botao botao-principal">Salvar alterações</button>
            <a href="{{ route('usuarios.index') }}">Cancelar</a>
        </div>
    </form>

    @unless ($usuario->is(auth()->user()))
        <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}" class="formulario zona-exclusao"
              data-confirmar="Remover o acesso de {{ $usuario->name }}? Os registros feitos por essa pessoa continuam no histórico.">
            @csrf
            @method('DELETE')
            <fieldset>
                <legend>Remover acesso</legend>
                <p class="explicacao">A pessoa deixa de conseguir entrar. Os cuidados que ela registrou continuam no histórico.</p>
                <button type="submit" class="botao botao-perigo">Remover acesso</button>
            </fieldset>
        </form>
    @endunless
@endsection
