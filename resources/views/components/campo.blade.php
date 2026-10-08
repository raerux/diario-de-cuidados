@props([
    'nome',
    'rotulo',
    'tipo' => 'text',
    'valor' => null,
    'opcional' => false,
    'ajuda' => null,
    'unidade' => null,
    'largo' => false,
])

@php
    $idCampo = 'campo-'.$nome;
    $descricao = trim(($ajuda ? $idCampo.'-ajuda ' : '').($errors->has($nome) ? $idCampo.'-erro' : ''));
@endphp

<div @class(['campo', 'ocupa-tudo' => $largo, 'com-erro' => $errors->has($nome)])>
    <label for="{{ $idCampo }}">
        {{ $rotulo }}
        @if ($opcional)
            <span class="opcional">(opcional)</span>
        @endif
    </label>

    @if ($tipo === 'textarea')
        <textarea id="{{ $idCampo }}" name="{{ $nome }}" rows="3"
            @if ($descricao) aria-describedby="{{ $descricao }}" @endif
            @if ($errors->has($nome)) aria-invalid="true" @endif
            {{ $attributes }}>{{ old($nome, $valor) }}</textarea>
    @else
        @if ($unidade)
            <div class="unidade">
        @endif
        <input type="{{ $tipo }}" id="{{ $idCampo }}" name="{{ $nome }}" value="{{ old($nome, $valor) }}"
            @if ($descricao) aria-describedby="{{ $descricao }}" @endif
            @if ($errors->has($nome)) aria-invalid="true" @endif
            {{ $attributes }}>
        @if ($unidade)
                <span>{{ $unidade }}</span>
            </div>
        @endif
    @endif

    @if ($ajuda)
        <span class="ajuda" id="{{ $idCampo }}-ajuda">{{ $ajuda }}</span>
    @endif

    @error($nome)
        <span class="erro" id="{{ $idCampo }}-erro">{{ $message }}</span>
    @enderror
</div>
