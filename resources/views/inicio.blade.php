<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.app-celular')
    <title>{{ config('app.name') }}: o cuidado de quem você ama, organizado</title>
    <meta name="description" content="Registre remédios, refeições, sinais vitais e todos os cuidados com idosos em um diário compartilhado pela família e pelos cuidadores. Leve à consulta um relatório com gráficos.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible+Next:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inicio.css') }}">
</head>

@php
    // Exemplos fictícios para ilustrar a página, desenhados com os mesmos componentes do sistema.
    $inicio = today()->subDays(13)->startOfDay();
    $fim = today()->endOfDay();
    $maximas = [132, 138, 148, 130, 152, 129, 140, 133, 136, 149, 141, 137, 142, 134];
    $minimas = [84, 80, 82, 83, 78, 80, 91, 93, 83, 87, 92, 79, 80, 83];
    $pontos = fn (array $valores, int $limite, int $piso) => collect($valores)->map(fn ($v, $i) => [
        'x' => $inicio->copy()->addDays($i)->setTime(9, 0),
        'y' => (float) $v,
        'fora' => $v > $limite || $v < $piso,
        'dica' => $inicio->copy()->addDays($i)->format('d/m').': '.$v.' mmHg',
    ])->all();
    $grafico = \App\Support\GraficoSvg::linha(
        [
            ['classe' => 'serie-1', 'pontos' => $pontos($maximas, 139, 90)],
            ['classe' => 'serie-2', 'pontos' => $pontos($minimas, 89, 60)],
        ],
        [['min' => 90.0, 'max' => 139.0], ['min' => 60.0, 'max' => 89.0]],
        $inicio,
        $fim,
        'Exemplo de gráfico de pressão arterial em 14 dias, com a faixa usual sombreada e as medições fora dela destacadas.',
    );
    $doses = ['ok', 'ok', 'ok', 'ok', 'ok', 'ok', 'ok', 'ok', 'ok', 'parcial', 'ok', 'ok', 'ok', 'ok'];
    $tipos = \App\Enums\TipoCuidado::cases();
@endphp

<body class="pagina-inicio">
    <a class="pular" href="#conteudo">Pular para o conteúdo</a>

    <header class="inicio-topo">
        <div class="inicio-largura inicio-topo-interno">
            <span class="marca"><x-marca /> {{ config('app.name') }}</span>
            <nav class="inicio-topo-acoes" aria-label="Acesso">
                <a class="inicio-link-entrar" href="{{ route('login') }}">Entrar</a>
                <a class="botao botao-principal botao-pequeno" href="{{ route('cadastro') }}">Criar meu acesso</a>
            </nav>
        </div>
    </header>

    <main id="conteudo">
        {{-- ---------- Abertura ---------- --}}
        <section class="heroi">
            <div class="inicio-largura heroi-grade">
                <div class="heroi-texto">
                    <p class="selo-heroi">Para famílias e cuidadores de pessoas idosas</p>
                    <h1>O cuidado de quem você ama, <span>organizado num só lugar.</span></h1>
                    <p class="heroi-sub">
                        Registre remédios, refeições, pressão e todos os cuidados do dia em um diário que a família
                        e os cuidadores compartilham. Saiba o que falta fazer hoje e leve à consulta um relatório com gráficos.
                    </p>
                    <div class="heroi-acoes">
                        <a class="botao botao-principal botao-grande" href="{{ route('cadastro') }}">Criar meu acesso</a>
                        <a class="botao botao-grande botao-claro" href="{{ route('login') }}">Já tenho acesso</a>
                    </div>
                    <ul class="heroi-garantias">
                        <li>Funciona no celular como aplicativo</li>
                        <li>Letra grande e fácil de ler</li>
                        <li>Cada registro mostra quem cuidou</li>
                    </ul>
                </div>

                {{-- Ilustração: a tela "Hoje" --}}
                <div class="heroi-ilustracao" aria-hidden="true">
                    <div class="janela">
                        <div class="janela-barra"><span></span><span></span><span></span></div>
                        <div class="janela-corpo">
                            <p class="janela-titulo">Hoje</p>
                            <p class="janela-sub">Maria · 3 cuidados feitos, 1 para fazer</p>

                            <div class="mini-registro tipo-medicacao status-pendente atrasado">
                                <b>08:00</b><i></i>
                                <div><strong>Medicação</strong> <em class="selo selo-atrasado">Atrasado</em><br>Losartana 50 mg</div>
                                <span class="mini-botao">Marcar como feito</span>
                            </div>
                            <div class="mini-registro tipo-higiene">
                                <b>07:30</b><i></i>
                                <div><strong>Higiene</strong><br>Banho com cadeira de banho</div>
                            </div>
                            <div class="mini-registro tipo-sinais_vitais">
                                <b>09:00</b><i></i>
                                <div><strong>Sinais vitais</strong><br>Pressão <u>148/92</u> · Saturação 97%</div>
                            </div>
                            <div class="mini-registro tipo-alimentacao">
                                <b>12:30</b><i></i>
                                <div><strong>Alimentação</strong><br>Almoço: comeu metade</div>
                            </div>
                        </div>
                    </div>
                    <div class="cartao-flutuante">
                        <span class="cartao-flutuante-icone"></span>
                        <div><strong>Pressão acima da faixa</strong><br>148/92 mmHg, hoje às 09:00</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ---------- O problema ---------- --}}
        <section class="faixa-problema" aria-labelledby="titulo-problema">
            <div class="inicio-largura">
                <h2 id="titulo-problema" class="titulo-secao">Cuidar já é trabalho demais para depender da memória</h2>
                <ul class="dores">
                    <li>
                        <strong>“Alguém deu o remédio da noite?”</strong>
                        <p>Quando várias pessoas se revezam, ninguém tem certeza do que já foi feito.</p>
                    </li>
                    <li>
                        <strong>Anotações espalhadas</strong>
                        <p>Caderno na gaveta, mensagem no grupo da família, papel colado na geladeira.</p>
                    </li>
                    <li>
                        <strong>“Como esteve a pressão?”</strong>
                        <p>Na consulta, o médico pergunta, e ninguém lembra das últimas semanas.</p>
                    </li>
                </ul>
            </div>
        </section>

        {{-- ---------- Como funciona ---------- --}}
        <section class="secao-inicio" aria-labelledby="titulo-como">
            <div class="inicio-largura">
                <p class="sobretitulo">Como funciona</p>
                <h2 id="titulo-como" class="titulo-secao">Três passos, todos os dias</h2>
                <ol class="passos">
                    <li>
                        <span class="passo-numero">1</span>
                        <h3>Registre em segundos</h3>
                        <p>Escolha o tipo de cuidado pela cor, como numa caixinha de remédios. Data, hora e quem cuidou já vêm preenchidos.</p>
                    </li>
                    <li>
                        <span class="passo-numero">2</span>
                        <h3>Acompanhe o dia</h3>
                        <p>A tela <em>Hoje</em> mostra o que falta fazer, destaca o que está atrasado e resolve com um toque em <em>Marcar como feito</em>.</p>
                    </li>
                    <li>
                        <span class="passo-numero">3</span>
                        <h3>Leve à consulta</h3>
                        <p>Com um clique, o sistema monta um relatório com gráficos, remédios dia a dia e o que merece atenção.</p>
                    </li>
                </ol>

                <div class="paleta-tipos" aria-label="Tipos de cuidado que dá para registrar">
                    @foreach ($tipos as $tipo)
                        <span class="tipo-{{ $tipo->value }}"><i aria-hidden="true"></i>{{ $tipo->label() }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ---------- Destaque: relatório ---------- --}}
        <section class="secao-inicio secao-relatorio" aria-labelledby="titulo-relatorio">
            <div class="inicio-largura relatorio-grade">
                <div>
                    <p class="sobretitulo">Relatório para consulta</p>
                    <h2 id="titulo-relatorio" class="titulo-secao">Chegue à consulta com as respostas na mão</h2>
                    <p class="texto-secao">
                        Escolha 7, 14 ou 30 dias e o Diário transforma os registros num relatório claro,
                        pronto para mostrar ao médico, imprimir ou salvar em PDF.
                    </p>
                    <ul class="lista-check">
                        <li><strong>Gráficos de evolução</strong> da pressão, glicemia, temperatura, batimentos e saturação, com a faixa usual em verde.</li>
                        <li><strong>Remédios dia a dia:</strong> veja num relance se alguma dose ficou para trás.</li>
                        <li><strong>Pontos para conversar</strong>, escritos automaticamente a partir do que foi anotado.</li>
                        <li><strong>Ocorrências e observações</strong> de quem cuidou, em ordem.</li>
                    </ul>
                </div>

                <figure class="amostra-relatorio">
                    <p class="amostra-cabecalho"><span>Relatório de cuidados</span> Maria · últimos 14 dias</p>
                    <ul class="pontos-atencao">
                        <li class="ponto-alerta">Pressão arterial: 7 medições fora da faixa usual, de 14 no período.</li>
                        <li class="ponto-falha">Losartana: 1 dose não foi dada (96% das doses foram dadas).</li>
                    </ul>
                    <div class="amostra-grafico">
                        <p class="amostra-rotulo">Pressão arterial <span>mmHg</span></p>
                        {!! $grafico !!}
                        <p class="sinal-rodape">
                            <span class="legenda serie-1">Máxima</span>
                            <span class="legenda serie-2">Mínima</span>
                            <span>Faixa usual em verde</span>
                        </p>
                    </div>
                    <div class="amostra-remedio">
                        <p class="amostra-rotulo">Losartana 50 mg <strong>96%</strong></p>
                        <span class="calendario" style="--dias: 14" aria-hidden="true">
                            @foreach ($doses as $estado)
                                <span class="dia-{{ $estado }}"></span>
                            @endforeach
                        </span>
                    </div>
                    <figcaption class="visualmente-oculto">Exemplo de relatório com dados fictícios.</figcaption>
                </figure>
            </div>
        </section>

        {{-- ---------- Recursos ---------- --}}
        <section class="secao-inicio" aria-labelledby="titulo-recursos">
            <div class="inicio-largura">
                <p class="sobretitulo">Tudo o que o dia a dia pede</p>
                <h2 id="titulo-recursos" class="titulo-secao">Feito para quem cuida, não para quem entende de tecnologia</h2>
                <ul class="recursos">
                    <li>
                        <h3>Alergias sempre à vista</h3>
                        <p>A ficha de cada idoso destaca alergias, condições de saúde, remédios de uso contínuo e o contato de emergência.</p>
                    </li>
                    <li>
                        <h3>Alerta de sinais fora da faixa</h3>
                        <p>Pressão, glicemia, febre ou saturação baixa aparecem em destaque assim que são registradas.</p>
                    </li>
                    <li>
                        <h3>Repetir com um toque</h3>
                        <p>Remédio de horário? Use <em>Repetir</em> e o registro sai pronto, com a hora de agora.</p>
                    </li>
                    <li>
                        <h3>Várias pessoas, um só diário</h3>
                        <p>Filhos, netos e cuidadores usam o mesmo sistema, e cada registro guarda quem fez.</p>
                    </li>
                    <li>
                        <h3>Histórico completo</h3>
                        <p>Encontre qualquer registro por idoso, tipo, situação, período ou palavra.</p>
                    </li>
                    <li>
                        <h3>Fácil de ler, fácil de tocar</h3>
                        <p>Fonte criada para baixa visão, botões grandes e textos em português do dia a dia.</p>
                    </li>
                </ul>
            </div>
        </section>

        {{-- ---------- Chamada final ---------- --}}
        <section class="chamada-final" aria-labelledby="titulo-chamada">
            <div class="inicio-largura">
                <h2 id="titulo-chamada">Menos esquecimento. Mais tranquilidade.</h2>
                <p>Crie seu acesso e comece a registrar hoje mesmo. Leva menos de um minuto.</p>
                <div class="heroi-acoes">
                    <a class="botao botao-grande botao-branco" href="{{ route('cadastro') }}">Criar meu acesso</a>
                    <a class="botao botao-grande botao-contorno" href="{{ route('login') }}">Já tenho acesso</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="inicio-rodape">
        <div class="inicio-largura">
            <span class="marca"><x-marca /> {{ config('app.name') }}</span>
            <p>
                O {{ config('app.name') }} organiza o que é registrado por quem cuida. Ele não faz diagnóstico
                e não substitui a orientação da equipe de saúde.
            </p>
        </div>
    </footer>
</body>
</html>
