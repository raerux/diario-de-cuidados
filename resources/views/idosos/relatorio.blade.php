@extends('layouts.app')

@section('titulo', 'Relatório de '.$idoso->nome_curto)

@php
    use App\Support\Datas;

    $totais = $relatorio->totais();
    $sinais = $relatorio->sinaisVitais();
    $remedios = $relatorio->medicacao();
    $adesao = $relatorio->adesaoGeral();
    $porTipo = $relatorio->porTipo();
    $ocorrencias = $relatorio->ocorrencias();
    $pontos = $relatorio->pontosDeAtencao();
    $sinaisFora = array_sum(array_column($sinais, 'fora'));
    $maiorTipo = $porTipo[0]['total'] ?? 1;
    $diasCompactos = $relatorio->periodo > 14;
@endphp

@section('conteudo')
    <div class="cabecalho-pagina nao-imprimir">
        <div>
            <a class="voltar" href="{{ route('idosos.show', $idoso) }}">Voltar para a ficha de {{ $idoso->nome_curto }}</a>
            <h1>Relatório para consulta</h1>
            <p class="subtitulo">
                Um resumo do que foi registrado, para levar ao médico ou enviar à família.
                Use “Imprimir” para ter no papel ou salvar em PDF.
            </p>
        </div>
        <div class="acoes">
            <nav class="seletor-periodo" aria-label="Período do relatório">
                @foreach (\App\Support\RelatorioSaude::PERIODOS as $opcao)
                    <a href="{{ route('idosos.relatorio', [$idoso, 'dias' => $opcao]) }}"
                       @if ($relatorio->periodo === $opcao) aria-current="true" @endif>{{ $opcao }} dias</a>
                @endforeach
            </nav>
            <button type="button" class="botao botao-principal" data-imprimir>Imprimir ou salvar PDF</button>
        </div>
    </div>

    <article class="relatorio">
        <header class="relatorio-cabecalho">
            <div>
                <p class="relatorio-sobretitulo">Relatório de cuidados</p>
                <h2 class="relatorio-nome">{{ $idoso->nome }}</h2>
                <p class="relatorio-identificacao">
                    {{ $idoso->idade }} anos · nascimento em {{ $idoso->data_nascimento->format('d/m/Y') }}
                    @if ($idoso->sexo) · {{ $idoso->sexo->label() }} @endif
                    @if ($idoso->tipo_sanguineo) · tipo sanguíneo {{ $idoso->tipo_sanguineo }} @endif
                </p>
            </div>
            <div class="relatorio-periodo">
                <strong>{{ $relatorio->inicio->format('d/m/Y') }} a {{ $relatorio->fim->format('d/m/Y') }}</strong>
                <span>Últimos {{ $relatorio->periodo }} dias</span>
                <span class="meta">Gerado em {{ now()->format('d/m/Y \à\s H:i') }} por {{ auth()->user()->name }}</span>
            </div>
        </header>

        <section class="relatorio-saude" aria-label="Dados de saúde">
            <div @class(['relatorio-caixa', 'relatorio-alergia' => $idoso->alergias])>
                <h3>Alergias</h3>
                <p>{{ $idoso->alergias ?: 'Nenhuma informada' }}</p>
            </div>
            <div class="relatorio-caixa">
                <h3>Condições de saúde</h3>
                <p>{{ $idoso->condicoes_saude ?: 'Nenhuma informada' }}</p>
            </div>
            <div class="relatorio-caixa">
                <h3>Medicamentos de uso contínuo</h3>
                <p>{{ $idoso->medicamentos_continuos ?: 'Nenhum informado' }}</p>
            </div>
        </section>

        <section class="relatorio-secao" aria-labelledby="titulo-atencao">
            <h3 id="titulo-atencao" class="relatorio-titulo">Pontos para conversar na consulta</h3>
            <ul class="pontos-atencao">
                @foreach ($pontos as $ponto)
                    <li class="ponto-{{ $ponto['nivel'] }}">{{ $ponto['texto'] }}</li>
                @endforeach
            </ul>
        </section>

        <section class="relatorio-secao" aria-label="Números do período">
            <dl class="numeros">
                <div>
                    <dt>Cuidados feitos</dt>
                    <dd>{{ $totais['realizados'] }}</dd>
                    <span>em {{ $totais['dias_com_registro'] }} de {{ $relatorio->periodo }} dias</span>
                </div>
                <div @class(['numero-alerta' => $adesao !== null && $adesao < 100])>
                    <dt>Remédios dados</dt>
                    <dd>{{ $adesao === null ? '—' : $adesao.'%' }}</dd>
                    <span>{{ $adesao === null ? 'nenhuma dose registrada' : 'das doses registradas' }}</span>
                </div>
                <div @class(['numero-alerta' => $sinaisFora > 0])>
                    <dt>Medições fora da faixa</dt>
                    <dd>{{ $sinaisFora }}</dd>
                    <span>{{ $sinais === [] ? 'nenhuma medição' : 'somando todos os sinais' }}</span>
                </div>
                <div @class(['numero-alerta' => $totais['nao_realizados'] + $totais['esquecidos'] > 0])>
                    <dt>Cuidados que não aconteceram</dt>
                    <dd>{{ $totais['nao_realizados'] + $totais['esquecidos'] }}</dd>
                    <span>não feitos ou sem registro</span>
                </div>
            </dl>
        </section>

        @if ($sinais !== [])
            <section class="relatorio-secao" aria-labelledby="titulo-evolucao">
                <h3 id="titulo-evolucao" class="relatorio-titulo">Evolução dos sinais vitais</h3>
                <p class="relatorio-explicacao">
                    A faixa verde é a faixa usual configurada no sistema. Pontos em laranja ficaram fora dela.
                    Na tela, passe o dedo ou o mouse sobre um ponto para ver o valor.
                </p>

                <div class="sinais-graficos">
                    @foreach ($sinais as $sinal)
                        <figure class="sinal-cartao">
                            <figcaption>
                                <h4>{{ $sinal['nome'] }} <span class="unidade-sinal">{{ $sinal['unidade'] }}</span></h4>
                                <dl class="sinal-numeros">
                                    <div><dt>Média</dt><dd>{{ $sinal['media'] }}</dd></div>
                                    <div><dt>Menor</dt><dd>{{ $sinal['minimo'] }}</dd></div>
                                    <div><dt>Maior</dt><dd>{{ $sinal['maximo'] }}</dd></div>
                                    <div><dt>Última</dt><dd>{{ $sinal['ultimo'] }}</dd></div>
                                </dl>
                            </figcaption>
                            {!! $sinal['grafico'] !!}
                            <p class="sinal-rodape">
                                @foreach ($sinal['legenda'] as $item)
                                    <span class="legenda {{ $item['classe'] }}">{{ $item['nome'] }}</span>
                                @endforeach
                                <span>{{ $sinal['medicoes'] === 1 ? '1 medição' : $sinal['medicoes'].' medições' }}</span>
                                <span @class(['texto-alerta' => $sinal['fora'] > 0])>
                                    {{ $sinal['fora'] === 0 ? 'todas na faixa' : ($sinal['fora'] === 1 ? '1 fora da faixa' : $sinal['fora'].' fora da faixa') }}
                                </span>
                                <span>faixa usual {{ $sinal['faixa'] }}</span>
                            </p>
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($remedios !== [])
            <section class="relatorio-secao" aria-labelledby="titulo-medicacao">
                <h3 id="titulo-medicacao" class="relatorio-titulo">Medicação, dia a dia</h3>
                <p class="relatorio-explicacao">
                    Cada quadrado é um dia.
                    <span class="chave-dia dia-ok"></span> todas as doses dadas,
                    <span class="chave-dia dia-parcial"></span> parte das doses,
                    <span class="chave-dia dia-falha"></span> nenhuma dose dada,
                    <span class="chave-dia dia-vazio"></span> sem registro.
                </p>

                <div class="tabela-envoltorio">
                    <table class="tabela-remedios">
                        <thead>
                            <tr>
                                <th scope="col">Remédio</th>
                                <th scope="col">Doses dadas</th>
                                <th scope="col" class="coluna-calendario">
                                    <span class="calendario calendario-cabecalho" style="--dias: {{ $relatorio->periodo }}" aria-hidden="true">
                                        @foreach ($relatorio->dias as $i => $dia)
                                            <span>{{ ! $diasCompactos || $i % 3 === 0 || $loop->last ? $dia->format('j') : '' }}</span>
                                        @endforeach
                                    </span>
                                    <span class="visualmente-oculto">Dias do período</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($remedios as $remedio)
                                @php $falhas = $remedio['nao_dadas'] + $remedio['esquecidas']; @endphp
                                <tr>
                                    <th scope="row">
                                        {{ $remedio['nome'] }}
                                        @if ($remedio['dosagem'])
                                            <span class="meta">{{ $remedio['dosagem'] }}</span>
                                        @endif
                                    </th>
                                    <td class="coluna-adesao">
                                        <strong @class(['texto-alerta' => $falhas > 0])>{{ $remedio['adesao'] }}%</strong>
                                        <span class="barra" aria-hidden="true"><span style="width: {{ $remedio['adesao'] }}%"></span></span>
                                        <span class="meta">
                                            {{ $remedio['dadas'] }} de {{ $remedio['dadas'] + $falhas }}
                                            @if ($remedio['nao_dadas']) · {{ $remedio['nao_dadas'] }} não {{ $remedio['nao_dadas'] === 1 ? 'dada' : 'dadas' }} @endif
                                            @if ($remedio['esquecidas']) · {{ $remedio['esquecidas'] }} sem registro @endif
                                        </span>
                                    </td>
                                    <td class="coluna-calendario">
                                        <span class="calendario" style="--dias: {{ $relatorio->periodo }}">
                                            @foreach ($remedio['dias'] as $dia)
                                                @php
                                                    $total = $dia['dadas'] + $dia['falhas'];
                                                    $dica = $dia['data']->format('d/m').': '.match ($dia['estado']) {
                                                        'vazio' => 'sem registro',
                                                        default => $dia['dadas'].' de '.$total.($total === 1 ? ' dose dada' : ' doses dadas'),
                                                    };
                                                @endphp
                                                <span class="dia-{{ $dia['estado'] }}" title="{{ $dica }}"><span class="visualmente-oculto">{{ $dica }}</span></span>
                                            @endforeach
                                        </span>
                                        <span class="calendario-limites" aria-hidden="true">
                                            <span>{{ $relatorio->inicio->format('d/m') }}</span>
                                            <span>{{ $relatorio->fim->format('d/m') }}</span>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($porTipo !== [])
            <section class="relatorio-secao" aria-labelledby="titulo-rotina">
                <h3 id="titulo-rotina" class="relatorio-titulo">Rotina de cuidados</h3>
                <ul class="rotina">
                    @foreach ($porTipo as $linha)
                        <li class="tipo-{{ $linha['tipo']->value }}">
                            <span class="rotina-nome">{{ $linha['tipo']->label() }}</span>
                            <span class="rotina-barra" aria-hidden="true"><span style="width: {{ round($linha['total'] / $maiorTipo * 100) }}%"></span></span>
                            <span class="rotina-total">
                                <strong>{{ $linha['total'] }}</strong>
                                <span class="meta">{{ number_format($linha['por_dia'], 1, ',', '') }} por dia</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="relatorio-secao" aria-labelledby="titulo-ocorrencias">
            <h3 id="titulo-ocorrencias" class="relatorio-titulo">Ocorrências e observações</h3>
            @if ($ocorrencias === [])
                <p class="relatorio-explicacao">Nada fora do comum foi registrado no período.</p>
            @else
                <ol class="ocorrencias">
                    @foreach ($ocorrencias as $ocorrencia)
                        <li class="ocorrencia-{{ $ocorrencia['nivel'] }}">
                            <span class="ocorrencia-quando">
                                {{ $ocorrencia['cuidado']->data_hora->format('d/m') }}
                                <small>{{ $ocorrencia['cuidado']->data_hora->format('H:i') }}</small>
                            </span>
                            <div>
                                <a href="{{ route('cuidados.show', $ocorrencia['cuidado']) }}"><strong>{{ $ocorrencia['titulo'] }}</strong></a>
                                @foreach ($ocorrencia['textos'] as $texto)
                                    <p>{{ $texto }}</p>
                                @endforeach
                                <p class="meta">Registrado por {{ $ocorrencia['cuidado']->responsavel }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        <footer class="relatorio-rodape">
            <p>
                Este relatório reúne apenas o que foi anotado no {{ config('app.name') }} por quem cuida.
                As faixas usuais são referências gerais para adultos e não substituem a avaliação da equipe de saúde.
            </p>
            @if ($idoso->contato_emergencia_nome)
                <p>Contato da família: {{ $idoso->contato_emergencia_nome }}@if ($idoso->contato_emergencia_telefone), {{ $idoso->contato_emergencia_telefone }}@endif</p>
            @endif
        </footer>
    </article>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-imprimir]').forEach(function (botao) {
            botao.addEventListener('click', function () { window.print(); });
        });
    </script>
@endpush
