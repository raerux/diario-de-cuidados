<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Gráfico de linha em SVG, montado no servidor (sem biblioteca de JavaScript).
 * Mostra a faixa usual sombreada e destaca os pontos que ficaram fora dela.
 * As cores vêm do CSS (classes .grafico-*), então funciona na tela e na impressão.
 */
class GraficoSvg
{
    private const LARGURA = 640;

    private const ALTURA = 210;

    private const MARGEM = ['topo' => 14, 'direita' => 14, 'baixo' => 30, 'esquerda' => 46];

    /**
     * @param  list<array{classe: string, pontos: list<array{x: CarbonInterface, y: float, fora: bool, dica: string}>}>  $series
     * @param  list<array{min: float, max: float}>  $faixas
     */
    public static function linha(
        array $series,
        array $faixas,
        CarbonInterface $inicio,
        CarbonInterface $fim,
        string $descricao,
        int $casas = 0,
    ): string {
        $valores = [];
        foreach ($series as $serie) {
            foreach ($serie['pontos'] as $ponto) {
                $valores[] = $ponto['y'];
            }
        }

        if ($valores === []) {
            return '';
        }

        foreach ($faixas as $faixa) {
            $valores[] = $faixa['min'];
            $valores[] = $faixa['max'];
        }

        [$yMin, $yMax, $passo] = self::escala(min($valores), max($valores));

        $m = self::MARGEM;
        $areaL = self::LARGURA - $m['esquerda'] - $m['direita'];
        $areaA = self::ALTURA - $m['topo'] - $m['baixo'];

        $t0 = $inicio->getTimestamp();
        $t1 = max($fim->getTimestamp(), $t0 + 1);

        $px = fn (CarbonInterface $data) => round($m['esquerda'] + ($data->getTimestamp() - $t0) / ($t1 - $t0) * $areaL, 1);
        $py = fn (float $valor) => round($m['topo'] + ($yMax - $valor) / ($yMax - $yMin) * $areaA, 1);

        $svg = [];
        $svg[] = sprintf(
            '<svg class="grafico" viewBox="0 0 %d %d" role="img" aria-label="%s" preserveAspectRatio="xMidYMid meet">',
            self::LARGURA, self::ALTURA, self::e($descricao)
        );

        // Faixa usual
        foreach ($faixas as $faixa) {
            $topo = $py(min($faixa['max'], $yMax));
            $base = $py(max($faixa['min'], $yMin));
            $svg[] = sprintf(
                '<rect class="grafico-faixa" x="%d" y="%s" width="%d" height="%s"/>',
                $m['esquerda'], $topo, $areaL, max(0, $base - $topo)
            );
        }

        // Grade horizontal e rótulos do eixo Y
        for ($valor = $yMin; $valor <= $yMax + $passo / 1000; $valor += $passo) {
            $y = $py($valor);
            $svg[] = sprintf(
                '<line class="grafico-grade" x1="%d" x2="%d" y1="%s" y2="%s"/>',
                $m['esquerda'], self::LARGURA - $m['direita'], $y, $y
            );
            $svg[] = sprintf(
                '<text class="grafico-eixo" x="%d" y="%s" text-anchor="end" dominant-baseline="middle">%s</text>',
                $m['esquerda'] - 8, $y, self::numero($valor, $passo < 1 ? 1 : 0)
            );
        }

        // Rótulos do eixo X (datas)
        $dias = (int) $inicio->copy()->startOfDay()->diffInDays($fim->copy()->startOfDay()) + 1;
        $intervalo = max(1, (int) ceil($dias / 7));
        for ($i = 0; $i < $dias; $i += $intervalo) {
            $dia = $inicio->copy()->startOfDay()->addDays($i)->addHours(12);
            $svg[] = sprintf(
                '<text class="grafico-eixo" x="%s" y="%d" text-anchor="middle">%s</text>',
                $px($dia), self::ALTURA - 8, $dia->format('d/m')
            );
        }

        // Séries
        foreach ($series as $serie) {
            $pontos = $serie['pontos'];
            if ($pontos === []) {
                continue;
            }

            $caminho = [];
            foreach ($pontos as $i => $ponto) {
                $caminho[] = ($i === 0 ? 'M' : 'L').$px($ponto['x']).' '.$py($ponto['y']);
            }

            $classe = self::e($serie['classe']);
            $svg[] = sprintf('<path class="grafico-linha %s" d="%s"/>', $classe, implode(' ', $caminho));

            foreach ($pontos as $ponto) {
                $svg[] = sprintf(
                    '<circle class="grafico-ponto %s%s" cx="%s" cy="%s" r="%s"><title>%s</title></circle>',
                    $classe,
                    $ponto['fora'] ? ' fora' : '',
                    $px($ponto['x']),
                    $py($ponto['y']),
                    $ponto['fora'] ? '5' : '3.5',
                    self::e($ponto['dica'])
                );
            }
        }

        $svg[] = '</svg>';

        return implode('', $svg);
    }

    /**
     * Limites "redondos" para o eixo Y e o passo entre as linhas de grade.
     *
     * @return array{0: float, 1: float, 2: float}
     */
    private static function escala(float $min, float $max): array
    {
        if ($max - $min < 1e-9) {
            $min -= 1;
            $max += 1;
        }

        $bruto = ($max - $min) / 4;
        $potencia = 10 ** floor(log10($bruto));
        $passo = $potencia * 10;
        foreach ([1, 2, 2.5, 5, 10] as $fator) {
            if ($fator * $potencia >= $bruto) {
                $passo = $fator * $potencia;
                break;
            }
        }

        return [floor($min / $passo) * $passo, ceil($max / $passo) * $passo, $passo];
    }

    private static function numero(float $valor, int $casas): string
    {
        return number_format($valor, $casas, ',', '.');
    }

    private static function e(string $texto): string
    {
        return htmlspecialchars($texto, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
