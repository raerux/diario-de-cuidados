@extends('layouts.app')

@section('titulo', $idoso->nome)

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <a class="voltar" href="{{ route('idosos.index') }}">Voltar para a lista de idosos</a>
            <h1>{{ $idoso->nome }}</h1>
            <p class="subtitulo">
                {{ $idoso->idade }} anos, nascimento em {{ $idoso->data_nascimento->format('d/m/Y') }}
                @if (! $idoso->ativo)
                    <span class="selo selo-inativo">Fora de acompanhamento</span>
                @endif
            </p>
        </div>
        <div class="acoes">
            @if ($idoso->ativo)
                <a class="botao botao-principal" href="{{ route('cuidados.create', ['idoso' => $idoso->id]) }}">Registrar cuidado</a>
            @endif
            <a class="botao" href="{{ route('idosos.edit', $idoso) }}">Editar cadastro</a>
        </div>
    </div>

    <div class="ficha">
        <aside class="ficha-dados" aria-label="Ficha de {{ $idoso->nome }}">
            @if ($idoso->alergias)
                <div class="alergia">
                    <strong>Alergias</strong>
                    <p>{{ $idoso->alergias }}</p>
                </div>
            @endif

            <h2>Saúde</h2>
            <dl>
                <dt>Condições de saúde</dt>
                <dd>{{ $idoso->condicoes_saude ?: 'Nenhuma informada' }}</dd>

                <dt>Medicamentos de uso contínuo</dt>
                <dd>{{ $idoso->medicamentos_continuos ?: 'Nenhum informado' }}</dd>

                @unless ($idoso->alergias)
                    <dt>Alergias</dt>
                    <dd>Nenhuma informada</dd>
                @endunless

                <dt>Tipo sanguíneo</dt>
                <dd>{{ $idoso->tipo_sanguineo ?: 'Não informado' }}</dd>
            </dl>

            <h2>Contato</h2>
            <dl>
                <dt>Em caso de emergência</dt>
                <dd>
                    {{ $idoso->contato_emergencia_nome ?: 'Não informado' }}
                    @if ($idoso->contato_emergencia_telefone)
                        <br><a href="tel:{{ preg_replace('/\D/', '', $idoso->contato_emergencia_telefone) }}">{{ $idoso->contato_emergencia_telefone }}</a>
                    @endif
                </dd>

                @if ($idoso->telefone)
                    <dt>Telefone do idoso</dt>
                    <dd><a href="tel:{{ preg_replace('/\D/', '', $idoso->telefone) }}">{{ $idoso->telefone }}</a></dd>
                @endif

                @if ($idoso->endereco)
                    <dt>Endereço</dt>
                    <dd>{{ $idoso->endereco }}</dd>
                @endif
            </dl>

            <h2>Dados pessoais</h2>
            <dl>
                <dt>CPF</dt>
                <dd>{{ $idoso->cpf_formatado ?: 'Não informado' }}</dd>

                <dt>Sexo</dt>
                <dd>{{ $idoso->sexo?->label() ?? 'Não informado' }}</dd>
            </dl>

            @if ($idoso->observacoes)
                <h2>Observações</h2>
                <dl>
                    <dd>{{ $idoso->observacoes }}</dd>
                </dl>
            @endif
        </aside>

        <div>
            @if ($ultimosSinais)
                <section class="ultimos-sinais" aria-labelledby="titulo-sinais">
                    <h2 id="titulo-sinais">Últimos sinais vitais</h2>
                    <p class="meta">
                        <a href="{{ route('cuidados.show', $ultimosSinais) }}">{{ \App\Support\Datas::rotuloDia($ultimosSinais->data_hora) }}, às {{ $ultimosSinais->data_hora->format('H:i') }}</a>,
                        por {{ $ultimosSinais->responsavel }}
                    </p>
                    <ul class="sinais">
                        @foreach ($ultimosSinais->sinaisVitais() as $sinal)
                            <li @class(['fora' => $sinal['fora']])>{{ $sinal['rotulo'] }} <strong>{{ $sinal['valor'] }}</strong></li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($pendentes->isNotEmpty())
                <section class="secao" aria-labelledby="titulo-pendentes">
                    <div class="secao-titulo">
                        <h2 id="titulo-pendentes">Para fazer</h2>
                        <span class="contagem">{{ $pendentes->count() === 1 ? '1 pendente' : $pendentes->count().' pendentes' }}</span>
                    </div>
                    <ul class="diario">
                        @foreach ($pendentes as $cuidado)
                            <li><x-registro :cuidado="$cuidado" :mostrar-idoso="false" :mostrar-data="! $cuidado->data_hora->isToday()" /></li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="secao" aria-labelledby="titulo-historico">
                <div class="secao-titulo">
                    <h2 id="titulo-historico">Histórico de cuidados</h2>
                    <span class="contagem">{{ $cuidados->total() === 1 ? '1 registro' : $cuidados->total().' registros' }}</span>
                </div>

                <nav class="filtro-tipos" aria-label="Filtrar por tipo de cuidado">
                    <a href="{{ route('idosos.show', $idoso) }}" @if (! $tipoFiltro) aria-current="true" @endif>Todos</a>
                    @foreach (\App\Enums\TipoCuidado::cases() as $tipo)
                        <a href="{{ route('idosos.show', [$idoso, 'tipo' => $tipo->value]) }}"
                           @if ($tipoFiltro === $tipo) aria-current="true" @endif>{{ $tipo->label() }}</a>
                    @endforeach
                </nav>

                @if ($cuidados->isEmpty())
                    <div class="vazio">
                        @if ($tipoFiltro)
                            <p>Nenhum registro de {{ mb_strtolower($tipoFiltro->label()) }} ainda.</p>
                        @else
                            <p>Nenhum cuidado registrado ainda.</p>
                        @endif
                        @if ($idoso->ativo)
                            <p><a class="botao" href="{{ route('cuidados.create', ['idoso' => $idoso->id, 'tipo' => $tipoFiltro?->value]) }}">Registrar cuidado</a></p>
                        @endif
                    </div>
                @else
                    @foreach ($cuidados->groupBy(fn ($c) => $c->data_hora->toDateString()) as $doDia)
                        <div class="dia">
                            <h3 class="dia-titulo">{{ \App\Support\Datas::rotuloDia($doDia->first()->data_hora) }}</h3>
                            <ul class="diario">
                                @foreach ($doDia as $cuidado)
                                    <li><x-registro :cuidado="$cuidado" :mostrar-idoso="false" :acoes="$idoso->ativo" /></li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach

                    {{ $cuidados->links() }}
                @endif
            </section>
        </div>
    </div>
@endsection
