@extends('layouts.app')

@section('titulo', 'Editar '.$idoso->nome)

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <a class="voltar" href="{{ route('idosos.show', $idoso) }}">Voltar para a ficha</a>
            <h1>Editar cadastro</h1>
            <p class="subtitulo">{{ $idoso->nome }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('idosos.update', $idoso) }}" class="formulario" novalidate>
        @csrf
        @method('PUT')
        @include('idosos._form')

        <div class="rodape-formulario">
            <button type="submit" class="botao botao-principal">Salvar alterações</button>
            <a href="{{ route('idosos.show', $idoso) }}">Cancelar</a>
        </div>
    </form>

    <form method="POST" action="{{ route('idosos.destroy', $idoso) }}" class="formulario zona-exclusao"
          data-confirmar="Excluir {{ $idoso->nome }} e todo o histórico de cuidados? Isso não pode ser desfeito.">
        @csrf
        @method('DELETE')
        <fieldset>
            <legend>Excluir cadastro</legend>
            <p class="explicacao">Apaga o cadastro e todos os registros de cuidado desta pessoa. Para manter o histórico, desmarque “Em acompanhamento” em vez de excluir.</p>
            <button type="submit" class="botao botao-perigo">Excluir {{ $idoso->nome_curto }}</button>
        </fieldset>
    </form>
@endsection
