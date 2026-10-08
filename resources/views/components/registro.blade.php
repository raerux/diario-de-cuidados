@props(['cuidado', 'mostrarIdoso' => true, 'mostrarData' => false, 'acoes' => true])

@php
    $pendente = $cuidado->status === \App\Enums\StatusCuidado::Pendente;
    $atrasado = $cuidado->estaAtrasado();
    $sinais = $cuidado->sinaisVitais();
@endphp

<article class="registro tipo-{{ $cuidado->tipo->value }} status-{{ $cuidado->status->value }}">
    <a class="registro-hora" href="{{ route('cuidados.show', $cuidado) }}"
       aria-label="Ver registro de {{ $cuidado->data_hora->format('d/m/Y') }} às {{ $cuidado->data_hora->format('H:i') }}">
        {{ $cuidado->data_hora->format('H:i') }}
        @if ($mostrarData)
            <small>{{ $cuidado->data_hora->format('d/m') }}</small>
        @endif
    </a>

    <div class="registro-marca" aria-hidden="true"></div>

    <div class="registro-corpo">
        <p class="registro-topo">
            <span class="registro-tipo">{{ $cuidado->tipo->label() }}</span>

            @if ($mostrarIdoso && $cuidado->idoso)
                <a class="registro-idoso" href="{{ route('idosos.show', $cuidado->idoso) }}">{{ $cuidado->idoso->nome_curto }}</a>
            @endif

            @if ($atrasado)
                <span class="selo selo-atrasado">Atrasado</span>
            @elseif ($cuidado->status !== \App\Enums\StatusCuidado::Realizado)
                <span class="selo selo-{{ $cuidado->status->value }}">{{ $cuidado->status->label() }}</span>
            @endif
        </p>

        @if ($cuidado->medicamento)
            <p class="registro-titulo">{{ $cuidado->medicamento }} {{ $cuidado->dosagem }}</p>
        @endif

        <p class="registro-desc">
            <a href="{{ route('cuidados.show', $cuidado) }}">{{ \Illuminate\Support\Str::limit($cuidado->descricao, 180) }}</a>
        </p>

        @if ($sinais)
            <ul class="sinais">
                @foreach ($sinais as $sinal)
                    <li @class(['fora' => $sinal['fora']])>{{ $sinal['rotulo'] }} <strong>{{ $sinal['valor'] }}</strong></li>
                @endforeach
            </ul>
        @endif

        <p class="registro-rodape">
            {{ $pendente ? 'Responsável:' : 'Por' }} {{ $cuidado->responsavel }}
        </p>
    </div>

    @if ($acoes)
        <div class="registro-acoes">
            @if ($pendente)
                <form method="POST" action="{{ route('cuidados.concluir', $cuidado) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="botao botao-pequeno botao-principal">Marcar como feito</button>
                </form>
            @else
                <a class="botao botao-pequeno botao-discreto" href="{{ route('cuidados.create', ['copiar' => $cuidado->id]) }}"
                   title="Registrar de novo, com o horário de agora">Repetir</a>
            @endif
        </div>
    @endif
</article>
