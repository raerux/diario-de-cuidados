<?php

namespace App\Http\Controllers;

use App\Enums\StatusCuidado;
use App\Enums\TipoCuidado;
use App\Models\Cuidado;
use App\Models\Idoso;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $hoje = now();

        $diario = Cuidado::with('idoso')
            ->doDia($hoje)
            ->where('status', '!=', StatusCuidado::Pendente->value)
            ->orderBy('data_hora')
            ->orderBy('id')
            ->get();

        $pendentes = Cuidado::with('idoso')
            ->pendentes()
            ->where('data_hora', '<=', $hoje->copy()->endOfDay())
            ->orderBy('data_hora')
            ->get();

        $proximos = Cuidado::with('idoso')
            ->pendentes()
            ->where('data_hora', '>', $hoje->copy()->endOfDay())
            ->orderBy('data_hora')
            ->limit(5)
            ->get();

        $foraDaFaixa = Cuidado::with('idoso')
            ->where('tipo', TipoCuidado::SinaisVitais->value)
            ->where('status', StatusCuidado::Realizado->value)
            ->where('data_hora', '>=', $hoje->copy()->subDays(7)->startOfDay())
            ->orderByDesc('data_hora')
            ->get()
            ->filter(fn (Cuidado $cuidado) => $cuidado->alertas() !== [])
            ->take(6);

        $idosos = Idoso::ativos()->orderBy('nome')->get();

        return view('dashboard', [
            'hoje' => $hoje,
            'diario' => $diario,
            'pendentes' => $pendentes,
            'proximos' => $proximos,
            'foraDaFaixa' => $foraDaFaixa,
            'idosos' => $idosos,
        ]);
    }
}
