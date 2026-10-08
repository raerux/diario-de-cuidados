@extends('layouts.app')

@section('titulo', $cuidado->tipo->label().' de '.$cuidado->idoso->nome_curto)

@php
    $pendente = $cuidado->status === \App\Enums\StatusCuidado::Pendente;
    $sinais = $cuidado->sinaisVitais();
    $alertas = $cuidado->alertas();
@endphp

@section('conteudo')
    <div class="cabecalho-pagina">
        <div>
            <a class="voltar" href="{{ route('idosos.show', $cuidado->idoso) }}">Voltar para a ficha de {{ $cuidado->idoso->nome_curto }}</a>
            <h1>{{ $cuidado->tipo->label() }}</h1>
            <p class="subtitulo">
                {{ \App\Support\Datas::rotuloDia($cuidado->data_hora) }}, às {{ $cuidado->data_hora->format('H:i') }}
                @if ($cuidado->estaAtrasado())
                    <span class="selo selo-atrasado">Atrasado</span>
                @else
                    <span class="selo selo-{{ $cuidado->status->value }}">{{ $cuidado->status->label() }}</span>
                @endif
            </p>
        </div>
        <div class="acoes">
            @if ($pendente)
                <form method="POST" action="{{ route('cuidados.concluir', $cuidado) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="botao botao-principal">Marcar como feito</button>
                </form>
            @elseif ($cuidado->idoso->ativo)
                <a class="botao" href="{{ route('cuidados.create', ['copiar' => $cuidado->id]) }}">Repetir agora</a>
            @endif
            <a class="botao" href="{{ route('cuidados.edit', $cuidado) }}">Editar</a>
        </div>
    </div>

    <article class="detalhe tipo-{{ $cuidado->tipo->value }}">
        @if ($alertas)
            <ul class="alertas">
                @foreach ($alertas as $alerta)
                    <li>{{ $alerta }}</li>
                @endforeach
            </ul>
        @endif

        <dl>
            <dt>Idoso</dt>
            <dd><a href="{{ route('idosos.show', $cuidado->idoso) }}">{{ $cuidado->idoso->nome }}</a></dd>

            @if ($cuidado->medicamento)
                <dt>Medicamento</dt>
                <dd><strong>{{ $cuidado->medicamento }}</strong> {{ $cuidado->dosagem }}</dd>
            @endif

            <dt>Descrição</dt>
            <dd>{{ $cuidado->descricao }}</dd>

            @foreach ($sinais as $sinal)
                <dt>{{ $sinal['rotulo'] }}</dt>
                <dd>
                    <strong>{{ $sinal['valor'] }}</strong>
                    @if ($sinal['fora'])
                        <span class="selo selo-atrasado">Fora da faixa usual</span>
                    @endif
                </dd>
            @endforeach

            @if ($cuidado->observacoes)
                <dt>Observações</dt>
                <dd>{{ $cuidado->observacoes }}</dd>
            @endif

            <dt>{{ $pendente ? 'Responsável' : 'Quem cuidou' }}</dt>
            <dd>{{ $cuidado->responsavel }}</dd>

            <dt>Data e hora</dt>
            <dd>{{ $cuidado->data_hora->format('d/m/Y') }}, às {{ $cuidado->data_hora->format('H:i') }}</dd>

            <dt>Registrado por</dt>
            <dd>
                {{ $cuidado->registradoPor?->name ?? 'Acesso removido' }},
                em {{ $cuidado->created_at->format('d/m/Y') }} às {{ $cuidado->created_at->format('H:i') }}
                @if ($cuidado->updated_at && $cuidado->updated_at->gt($cuidado->created_at))
                    <br><span class="ajuda">Editado em {{ $cuidado->updated_at->format('d/m/Y') }} às {{ $cuidado->updated_at->format('H:i') }}</span>
                @endif
            </dd>
        </dl>
    </article>

    <form method="POST" action="{{ route('cuidados.destroy', $cuidado) }}" class="formulario zona-exclusao"
          data-confirmar="Excluir este registro de {{ mb_strtolower($cuidado->tipo->label()) }}? Isso não pode ser desfeito.">
        @csrf
        @method('DELETE')
        <button type="submit" class="botao botao-perigo">Excluir registro</button>
    </form>
@endsection
