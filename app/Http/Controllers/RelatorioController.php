<?php

namespace App\Http\Controllers;

use App\Models\Idoso;
use App\Support\RelatorioSaude;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RelatorioController extends Controller
{
    /** Relatório do período para levar à consulta (pronto para imprimir ou salvar em PDF). */
    public function __invoke(Request $request, Idoso $idoso): View
    {
        $periodo = (int) $request->query('dias', (string) RelatorioSaude::PERIODO_PADRAO);

        if (! in_array($periodo, RelatorioSaude::PERIODOS, true)) {
            $periodo = RelatorioSaude::PERIODO_PADRAO;
        }

        return view('idosos.relatorio', [
            'idoso' => $idoso,
            'relatorio' => new RelatorioSaude($idoso, $periodo),
        ]);
    }
}
