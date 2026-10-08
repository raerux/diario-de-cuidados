@extends('layouts.app')

@section('titulo', 'Idosos')

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <h1>Idosos</h1>
            <p class="subtitulo">Cadastro, condições de saúde e contatos de emergência de cada pessoa acompanhada.</p>
        </div>
        <div class="acoes">
            <a class="botao botao-principal" href="{{ route('idosos.create') }}">Cadastrar idoso</a>
        </div>
    </div>

    <form method="GET" action="{{ route('idosos.index') }}" class="filtros" role="search">
        <div class="campo">
            <label for="q">Buscar por nome ou CPF</label>
            <input type="search" id="q" name="q" value="{{ $q }}">
        </div>
        <div class="campo">
            <label for="situacao">Mostrar</label>
            <select id="situacao" name="situacao">
                <option value="ativos" @selected($situacao === 'ativos')>Em acompanhamento</option>
                <option value="inativos" @selected($situacao === 'inativos')>Fora de acompanhamento</option>
                <option value="todos" @selected($situacao === 'todos')>Todos</option>
            </select>
        </div>
        <div class="filtros-acoes">
            <button type="submit" class="botao">Buscar</button>
            @if ($q !== '' || $situacao !== 'ativos')
                <a href="{{ route('idosos.index') }}">Limpar</a>
            @endif
        </div>
    </form>

    @if ($idosos->isEmpty())
        <div class="vazio">
            @if ($q !== '')
                <p>Ninguém encontrado para “{{ $q }}”. Confira a grafia ou busque pelo CPF.</p>
            @else
                <p>Nenhum idoso nesta lista.</p>
                <p><a class="botao botao-principal" href="{{ route('idosos.create') }}">Cadastrar idoso</a></p>
            @endif
        </div>
    @else
        <div class="tabela-envoltorio">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Idade</th>
                        <th scope="col" class="esconder-celular">Contato de emergência</th>
                        <th scope="col" class="esconder-celular">Último cuidado</th>
                        <th scope="col"><span class="visualmente-oculto">Ações</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($idosos as $idoso)
                        <tr>
                            <td>
                                <a href="{{ route('idosos.show', $idoso) }}"><strong>{{ $idoso->nome }}</strong></a>
                                @if (! $idoso->ativo)
                                    <span class="selo selo-inativo">Fora de acompanhamento</span>
                                @endif
                                @if ($idoso->alergias)
                                    <span class="meta meta-alerta">Alergia: {{ \Illuminate\Support\Str::limit(preg_replace('/\R+/', ', ', trim($idoso->alergias)), 60) }}</span>
                                @endif
                                @if ($idoso->pendentes_count > 0)
                                    <span class="meta">{{ $idoso->pendentes_count === 1 ? '1 cuidado pendente' : $idoso->pendentes_count.' cuidados pendentes' }}</span>
                                @endif
                            </td>
                            <td>{{ $idoso->idade }} anos</td>
                            <td class="esconder-celular">
                                {{ $idoso->contato_emergencia_nome ?? '—' }}
                                @if ($idoso->contato_emergencia_telefone)
                                    <span class="meta"><a href="tel:{{ preg_replace('/\D/', '', $idoso->contato_emergencia_telefone) }}">{{ $idoso->contato_emergencia_telefone }}</a></span>
                                @endif
                            </td>
                            <td class="esconder-celular">
                                @if ($idoso->ultimo_cuidado_em)
                                    {{ \App\Support\Datas::rotuloDia($idoso->ultimo_cuidado_em) }}
                                    <span class="meta">às {{ $idoso->ultimo_cuidado_em->format('H:i') }}</span>
                                @else
                                    <span class="meta">Nenhum ainda</span>
                                @endif
                            </td>
                            <td class="acoes-celula">
                                @if ($idoso->ativo)
                                    <a class="botao botao-pequeno" href="{{ route('cuidados.create', ['idoso' => $idoso->id]) }}"
                                       aria-label="Registrar cuidado para {{ $idoso->nome }}">Registrar cuidado</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $idosos->links() }}
    @endif
@endsection
