@extends('layouts.app')

@section('titulo', 'Acessos')

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <h1>Acessos</h1>
            <p class="subtitulo">Pessoas que podem entrar no sistema: familiares, cuidadores e profissionais de saúde. Cada registro de cuidado guarda quem o fez.</p>
        </div>
        <div class="acoes">
            <a class="botao botao-principal" href="{{ route('usuarios.create') }}">Criar acesso</a>
        </div>
    </div>

    <div class="tabela-envoltorio">
        <table>
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">E-mail</th>
                    <th scope="col" class="esconder-celular">Criado em</th>
                    <th scope="col"><span class="visualmente-oculto">Ações</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    <tr>
                        <td>
                            <strong>{{ $usuario->name }}</strong>
                            @if ($usuario->is(auth()->user()))
                                <span class="meta">Você</span>
                            @endif
                        </td>
                        <td>{{ $usuario->email }}</td>
                        <td class="esconder-celular">{{ $usuario->created_at?->format('d/m/Y') }}</td>
                        <td class="acoes-celula">
                            <a class="botao botao-pequeno" href="{{ route('usuarios.edit', $usuario) }}">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $usuarios->links() }}
@endsection
