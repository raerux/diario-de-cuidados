@extends('layouts.app')

@section('titulo', 'Hoje')

@php
    $feitos = $diario->filter(fn ($c) => $c->status === \App\Enums\StatusCuidado::Realizado)->count();
    $faltam = $pendentes->count();
    $totalIdosos = $idosos->count();

    $resumo = $totalIdosos === 1 ? '1 idoso em acompanhamento. ' : "{$totalIdosos} idosos em acompanhamento. ";
    $resumo .= match (true) {
        $feitos === 0 => 'Nenhum cuidado registrado hoje',
        $feitos === 1 => '1 cuidado feito hoje',
        default => "{$feitos} cuidados feitos hoje",
    };
    $resumo .= match (true) {
        $faltam === 0 => '.',
        $faltam === 1 => ' e 1 para fazer.',
        default => " e {$faltam} para fazer.",
    };
@endphp

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <h1>Hoje</h1>
            <p class="data-extenso">{{ \App\Support\Datas::extenso($hoje) }}</p>
            @if ($totalIdosos > 0)
                <p class="resumo-dia">{{ $resumo }}</p>
            @endif
        </div>
        @if ($totalIdosos > 0)
            <div class="acoes">
                <a class="botao botao-principal" href="{{ route('cuidados.create', ['voltar' => 'painel']) }}">Registrar cuidado</a>
            </div>
        @endif
    </div>

    @if ($totalIdosos === 0)
        <div class="vazio">
            <p>Ainda não há idosos cadastrados. Comece pelo cadastro e depois registre os cuidados do dia.</p>
            <p><a class="botao botao-principal" href="{{ route('idosos.create') }}">Cadastrar idoso</a></p>
        </div>
    @else
        <div class="hoje">
            <div>
                @if ($pendentes->isNotEmpty())
                    <section class="secao" aria-labelledby="titulo-pendentes">
                        <div class="secao-titulo">
                            <h2 id="titulo-pendentes">Para fazer</h2>
                            <span class="contagem">{{ $faltam === 1 ? '1 pendente' : "{$faltam} pendentes" }}</span>
                        </div>
                        <ul class="diario">
                            @foreach ($pendentes as $cuidado)
                                <li><x-registro :cuidado="$cuidado" :mostrar-data="! $cuidado->data_hora->isToday()" /></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section class="secao" aria-labelledby="titulo-diario">
                    <div class="secao-titulo">
                        <h2 id="titulo-diario">O que já foi feito</h2>
                        <span class="contagem">{{ $diario->count() === 1 ? '1 registro' : $diario->count().' registros' }}</span>
                    </div>

                    @if ($diario->isEmpty())
                        <div class="vazio">
                            <p>Nada registrado hoje ainda.</p>
                            <p><a class="botao" href="{{ route('cuidados.create', ['voltar' => 'painel']) }}">Registrar cuidado</a></p>
                        </div>
                    @else
                        <ul class="diario">
                            @foreach ($diario as $cuidado)
                                <li><x-registro :cuidado="$cuidado" /></li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            <aside class="lateral">
                @if ($foraDaFaixa->isNotEmpty())
                    <section class="bloco" aria-labelledby="titulo-fora">
                        <h2 id="titulo-fora">Sinais fora da faixa nos últimos 7 dias</h2>
                        <ul class="lista-simples">
                            @foreach ($foraDaFaixa as $cuidado)
                                <li>
                                    <a href="{{ route('cuidados.show', $cuidado) }}"><strong>{{ $cuidado->idoso->nome_curto }}</strong></a>
                                    <span class="meta">{{ \App\Support\Datas::rotuloDia($cuidado->data_hora) }}, {{ $cuidado->data_hora->format('H:i') }}</span>
                                    @foreach ($cuidado->alertas() as $alerta)
                                        <div>{{ $alerta }}</div>
                                    @endforeach
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($proximos->isNotEmpty())
                    <section class="bloco" aria-labelledby="titulo-proximos">
                        <h2 id="titulo-proximos">Agendados para os próximos dias</h2>
                        <ul class="lista-simples">
                            @foreach ($proximos as $cuidado)
                                <li>
                                    <a href="{{ route('cuidados.show', $cuidado) }}"><strong>{{ $cuidado->tipo->label() }}</strong></a>,
                                    {{ $cuidado->idoso->nome_curto }}
                                    <span class="meta">{{ \App\Support\Datas::rotuloDia($cuidado->data_hora) }}, {{ $cuidado->data_hora->format('H:i') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section class="bloco" aria-labelledby="titulo-atalhos">
                    <h2 id="titulo-atalhos">Registrar para</h2>
                    <ul class="lista-simples atalhos-idoso">
                        @foreach ($idosos as $idoso)
                            <li>
                                <a href="{{ route('idosos.show', $idoso) }}">{{ $idoso->nome_curto }}</a>
                                <a class="botao botao-pequeno" href="{{ route('cuidados.create', ['idoso' => $idoso->id, 'voltar' => 'painel']) }}"
                                   aria-label="Registrar cuidado para {{ $idoso->nome }}">Registrar</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    @endif
@endsection
