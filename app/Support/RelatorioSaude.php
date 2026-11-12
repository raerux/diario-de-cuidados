<?php

namespace App\Support;

use App\Enums\StatusCuidado;
use App\Enums\TipoCuidado;
use App\Models\Cuidado;
use App\Models\Idoso;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Resumo de um período de cuidados de um idoso, pensado para levar à consulta:
 * evolução dos sinais vitais, regularidade da medicação e ocorrências.
 *
 * Só descreve o que foi registrado; não faz diagnóstico.
 */
class RelatorioSaude
{
    public const PERIODOS = [7, 14, 30];

    public const PERIODO_PADRAO = 14;

    public readonly Carbon $inicio;

    public readonly Carbon $fim;

    /** @var Collection<int, Cuidado> */
    public readonly Collection $cuidados;

    /** @var list<Carbon> */
    public readonly array $dias;

    /** @var array<string, mixed> resultados já calculados */
    private array $memo = [];

    public function __construct(public readonly Idoso $idoso, public readonly int $periodo)
    {
        $this->fim = today()->endOfDay();
        $this->inicio = today()->subDays($periodo - 1)->startOfDay();

        $this->cuidados = $idoso->cuidados()
            ->whereBetween('data_hora', [$this->inicio, $this->fim])
            ->orderBy('data_hora')
            ->orderBy('id')
            ->get();

        $dias = [];
        for ($dia = $this->inicio->copy(); $dia->lte($this->fim); $dia->addDay()) {
            $dias[] = $dia->copy();
        }
        $this->dias = $dias;
    }

    /** Cuidados que já deveriam ter acontecido e ficaram como pendentes. */
    private function esquecido(Cuidado $cuidado): bool
    {
        return $cuidado->estaAtrasado();
    }

    /** @return array{realizados: int, nao_realizados: int, esquecidos: int, dias_com_registro: int} */
    public function totais(): array
    {
        $realizados = $this->cuidados->where('status', StatusCuidado::Realizado);

        return [
            'realizados' => $realizados->count(),
            'nao_realizados' => $this->cuidados->where('status', StatusCuidado::NaoRealizado)->count(),
            'esquecidos' => $this->cuidados->filter(fn (Cuidado $c) => $this->esquecido($c))->count(),
            'dias_com_registro' => $realizados->map(fn (Cuidado $c) => $c->data_hora->toDateString())->unique()->count(),
        ];
    }

    /**
     * Quantos cuidados de cada tipo foram feitos no período, do mais frequente ao menos.
     *
     * @return list<array{tipo: TipoCuidado, total: int, por_dia: float}>
     */
    public function porTipo(): array
    {
        return $this->cuidados
            ->where('status', StatusCuidado::Realizado)
            ->groupBy(fn (Cuidado $c) => $c->tipo->value)
            ->map(fn (Collection $grupo) => [
                'tipo' => $grupo->first()->tipo,
                'total' => $grupo->count(),
                'por_dia' => round($grupo->count() / $this->periodo, 1),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * Um bloco por sinal vital medido no período: estatísticas e gráfico.
     *
     * @return list<array{chave: string, nome: string, unidade: string, medicoes: int, fora: int, media: string, minimo: string, maximo: string, ultimo: string, faixa: string, grafico: string, legenda: list<array{classe: string, nome: string}>}>
     */
    public function sinaisVitais(): array
    {
        return $this->memo['sinais'] ??= $this->calcularSinaisVitais();
    }

    /** @return list<array<string, mixed>> */
    private function calcularSinaisVitais(): array
    {
        $medicoes = $this->cuidados
            ->where('tipo', TipoCuidado::SinaisVitais)
            ->where('status', StatusCuidado::Realizado)
            ->values();

        $faixas = config('cuidados.faixas', []);
        $blocos = [];

        // Pressão: duas linhas (máxima e mínima) no mesmo gráfico.
        $comPressao = $medicoes->filter(fn (Cuidado $c) => $c->pressao_sistolica !== null && $c->pressao_diastolica !== null)->values();
        if ($comPressao->isNotEmpty()) {
            $fs = $faixas['pressao_sistolica'];
            $fd = $faixas['pressao_diastolica'];
            $foraS = fn (Cuidado $c) => $c->pressao_sistolica < $fs['min'] || $c->pressao_sistolica > $fs['max'];
            $foraD = fn (Cuidado $c) => $c->pressao_diastolica < $fd['min'] || $c->pressao_diastolica > $fd['max'];
            $dica = fn (Cuidado $c) => $c->data_hora->format('d/m H:i').': '.$c->pressao_arterial.' mmHg';

            $ultima = $comPressao->last();

            $blocos[] = [
                'chave' => 'pressao',
                'nome' => 'Pressão arterial',
                'unidade' => 'mmHg',
                'medicoes' => $comPressao->count(),
                'fora' => $comPressao->filter(fn (Cuidado $c) => $foraS($c) || $foraD($c))->count(),
                'media' => round($comPressao->avg('pressao_sistolica')).'/'.round($comPressao->avg('pressao_diastolica')),
                'minimo' => $comPressao->min('pressao_sistolica').'/'.$comPressao->min('pressao_diastolica'),
                'maximo' => $comPressao->max('pressao_sistolica').'/'.$comPressao->max('pressao_diastolica'),
                'ultimo' => $ultima->pressao_arterial,
                'faixa' => "{$fs['min']} a {$fs['max']} / {$fd['min']} a {$fd['max']}",
                'grafico' => GraficoSvg::linha(
                    [
                        ['classe' => 'serie-1', 'pontos' => $comPressao->map(fn (Cuidado $c) => [
                            'x' => $c->data_hora, 'y' => (float) $c->pressao_sistolica, 'fora' => $foraS($c), 'dica' => $dica($c),
                        ])->all()],
                        ['classe' => 'serie-2', 'pontos' => $comPressao->map(fn (Cuidado $c) => [
                            'x' => $c->data_hora, 'y' => (float) $c->pressao_diastolica, 'fora' => $foraD($c), 'dica' => $dica($c),
                        ])->all()],
                    ],
                    [
                        ['min' => (float) $fs['min'], 'max' => (float) $fs['max']],
                        ['min' => (float) $fd['min'], 'max' => (float) $fd['max']],
                    ],
                    $this->inicio,
                    $this->fim,
                    "Pressão arterial em {$comPressao->count()} medições. Média {$this->numero($comPressao->avg('pressao_sistolica'))} por {$this->numero($comPressao->avg('pressao_diastolica'))} mmHg.",
                ),
                'legenda' => [
                    ['classe' => 'serie-1', 'nome' => 'Máxima (sistólica)'],
                    ['classe' => 'serie-2', 'nome' => 'Mínima (diastólica)'],
                ],
            ];
        }

        $simples = [
            'frequencia_cardiaca' => ['Batimentos cardíacos', 0],
            'temperatura' => ['Temperatura', 1],
            'glicemia' => ['Glicemia', 0],
            'saturacao' => ['Saturação de oxigênio', 0],
        ];

        foreach ($simples as $campo => [$nome, $casas]) {
            $faixa = $faixas[$campo];
            $comValor = $medicoes->filter(fn (Cuidado $c) => $c->{$campo} !== null)->values();

            if ($comValor->isEmpty()) {
                continue;
            }

            $valores = $comValor->map(fn (Cuidado $c) => (float) $c->{$campo});
            $fora = fn (float $v) => $v < $faixa['min'] || $v > $faixa['max'];
            $formatar = fn (float $v) => $this->numero($v, $casas);

            $blocos[] = [
                'chave' => $campo,
                'nome' => $nome,
                'unidade' => $faixa['unidade'],
                'medicoes' => $comValor->count(),
                'fora' => $valores->filter($fora)->count(),
                'media' => $formatar($valores->avg()),
                'minimo' => $formatar($valores->min()),
                'maximo' => $formatar($valores->max()),
                'ultimo' => $formatar($valores->last()),
                'faixa' => $formatar($faixa['min']).' a '.$formatar($faixa['max']),
                'grafico' => GraficoSvg::linha(
                    [['classe' => 'serie-1', 'pontos' => $comValor->map(fn (Cuidado $c) => [
                        'x' => $c->data_hora,
                        'y' => (float) $c->{$campo},
                        'fora' => $fora((float) $c->{$campo}),
                        'dica' => $c->data_hora->format('d/m H:i').': '.$formatar((float) $c->{$campo}).' '.$faixa['unidade'],
                    ])->all()]],
                    [['min' => (float) $faixa['min'], 'max' => (float) $faixa['max']]],
                    $this->inicio,
                    $this->fim,
                    "{$nome} em {$comValor->count()} medições. Média {$formatar($valores->avg())} {$faixa['unidade']}.",
                    $casas,
                ),
                'legenda' => [],
            ];
        }

        return $blocos;
    }

    /**
     * Regularidade de cada remédio: doses dadas, não dadas e esquecidas, dia a dia.
     *
     * @return list<array{nome: string, dosagem: ?string, dadas: int, nao_dadas: int, esquecidas: int, adesao: ?int, dias: list<array{data: Carbon, dadas: int, falhas: int, estado: string}>}>
     */
    public function medicacao(): array
    {
        return $this->memo['medicacao'] ??= $this->calcularMedicacao();
    }

    /** @return list<array<string, mixed>> */
    private function calcularMedicacao(): array
    {
        $doses = $this->cuidados
            ->where('tipo', TipoCuidado::Medicacao)
            ->filter(fn (Cuidado $c) => $c->status !== StatusCuidado::Pendente || $this->esquecido($c));

        return $doses
            ->groupBy(fn (Cuidado $c) => mb_strtolower(trim((string) $c->medicamento)) ?: '—')
            ->map(function (Collection $grupo) {
                $dadas = $grupo->where('status', StatusCuidado::Realizado)->count();
                $naoDadas = $grupo->where('status', StatusCuidado::NaoRealizado)->count();
                $esquecidas = $grupo->where('status', StatusCuidado::Pendente)->count();
                $previstas = $dadas + $naoDadas + $esquecidas;

                $porDia = $grupo->groupBy(fn (Cuidado $c) => $c->data_hora->toDateString());

                $dias = array_map(function (Carbon $dia) use ($porDia) {
                    $doDia = $porDia->get($dia->toDateString(), collect());
                    $dadas = $doDia->where('status', StatusCuidado::Realizado)->count();
                    $falhas = $doDia->count() - $dadas;

                    return [
                        'data' => $dia,
                        'dadas' => $dadas,
                        'falhas' => $falhas,
                        'estado' => match (true) {
                            $doDia->isEmpty() => 'vazio',
                            $falhas === 0 => 'ok',
                            $dadas === 0 => 'falha',
                            default => 'parcial',
                        },
                    ];
                }, $this->dias);

                $nome = trim((string) $grupo->first()->medicamento);

                return [
                    'nome' => $nome !== '' ? $nome : 'Remédio sem nome informado',
                    'dosagem' => $grupo->pluck('dosagem')->filter()->countBy()->sortDesc()->keys()->first(),
                    'dadas' => $dadas,
                    'nao_dadas' => $naoDadas,
                    'esquecidas' => $esquecidas,
                    'adesao' => $previstas > 0 ? (int) floor($dadas / $previstas * 100) : null,
                    'dias' => $dias,
                ];
            })
            ->sortBy('nome')
            ->values()
            ->all();
    }

    /** Percentual de todas as doses do período que foram dadas, ou null se não houve doses. */
    public function adesaoGeral(): ?int
    {
        $dadas = 0;
        $previstas = 0;
        foreach ($this->medicacao() as $remedio) {
            $dadas += $remedio['dadas'];
            $previstas += $remedio['dadas'] + $remedio['nao_dadas'] + $remedio['esquecidas'];
        }

        return $previstas > 0 ? (int) floor($dadas / $previstas * 100) : null;
    }

    /**
     * O que merece atenção, mais recente primeiro: sinais fora da faixa, cuidados
     * que não aconteceram e observações anotadas por quem cuidou.
     *
     * @return list<array{cuidado: Cuidado, nivel: string, titulo: string, textos: list<string>}>
     */
    public function ocorrencias(): array
    {
        $ocorrencias = [];

        foreach ($this->cuidados->sortByDesc('data_hora') as $cuidado) {
            if ($cuidado->status === StatusCuidado::Realizado && $cuidado->alertas() !== []) {
                $ocorrencias[] = [
                    'cuidado' => $cuidado,
                    'nivel' => 'alerta',
                    'titulo' => 'Sinal fora da faixa',
                    'textos' => array_filter([...$cuidado->alertas(), $cuidado->observacoes]),
                ];
            } elseif ($cuidado->status === StatusCuidado::NaoRealizado) {
                $ocorrencias[] = [
                    'cuidado' => $cuidado,
                    'nivel' => 'falha',
                    'titulo' => $cuidado->tipo->label().' não foi feito',
                    'textos' => array_filter([$this->rotulo($cuidado), $cuidado->observacoes]),
                ];
            } elseif ($this->esquecido($cuidado)) {
                $ocorrencias[] = [
                    'cuidado' => $cuidado,
                    'nivel' => 'falha',
                    'titulo' => $cuidado->tipo->label().' ficou sem registro',
                    'textos' => [$this->rotulo($cuidado).' (continua como pendente)'],
                ];
            } elseif ($cuidado->status === StatusCuidado::Realizado && filled($cuidado->observacoes)) {
                $ocorrencias[] = [
                    'cuidado' => $cuidado,
                    'nivel' => 'nota',
                    'titulo' => 'Observação em '.mb_strtolower($cuidado->tipo->label()),
                    'textos' => [$cuidado->observacoes],
                ];
            }
        }

        return $ocorrencias;
    }

    /**
     * Frases curtas para abrir a conversa na consulta, montadas a partir dos números.
     *
     * @return list<array{nivel: string, texto: string}>
     */
    public function pontosDeAtencao(): array
    {
        $pontos = [];

        foreach ($this->sinaisVitais() as $sinal) {
            if ($sinal['fora'] === 0) {
                continue;
            }
            $vezes = $sinal['fora'] === 1 ? '1 medição' : "{$sinal['fora']} medições";
            $pontos[] = [
                'nivel' => 'alerta',
                'texto' => "{$sinal['nome']}: {$vezes} fora da faixa usual, de {$sinal['medicoes']} no período (média {$sinal['media']} {$sinal['unidade']}).",
            ];
        }

        foreach ($this->medicacao() as $remedio) {
            $falhas = $remedio['nao_dadas'] + $remedio['esquecidas'];
            if ($falhas === 0) {
                continue;
            }
            $doses = $falhas === 1 ? '1 dose não foi dada' : "{$falhas} doses não foram dadas";
            $pontos[] = [
                'nivel' => 'falha',
                'texto' => "{$remedio['nome']}: {$doses} ({$remedio['adesao']}% das doses registradas foram dadas).",
            ];
        }

        $semMedicao = $this->cuidados
            ->where('tipo', TipoCuidado::SinaisVitais)
            ->where('status', StatusCuidado::Realizado)
            ->isEmpty();
        if ($semMedicao) {
            $pontos[] = ['nivel' => 'nota', 'texto' => 'Nenhuma medição de sinais vitais registrada no período.'];
        }

        if ($pontos === []) {
            $pontos[] = ['nivel' => 'ok', 'texto' => 'Nenhum sinal fora da faixa e nenhuma dose de remédio deixou de ser dada no período.'];
        }

        return $pontos;
    }

    private function rotulo(Cuidado $cuidado): string
    {
        $remedio = trim(($cuidado->medicamento ?? '').' '.($cuidado->dosagem ?? ''));

        return $remedio !== '' ? "{$remedio}. {$cuidado->descricao}" : $cuidado->descricao;
    }

    private function numero(float|int|null $valor, int $casas = 0): string
    {
        return number_format((float) $valor, $casas, ',', '.');
    }
}
