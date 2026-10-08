@php
    $tipoAtual = old('tipo', $cuidado->tipo?->value);
    $statusAtual = old('status', $cuidado->status?->value);
@endphp

@if ($errors->any())
    <div class="aviso aviso-erro" role="alert">
        Confira os campos destacados antes de salvar.
    </div>
@endif

@if (! empty($voltar))
    <input type="hidden" name="voltar" value="{{ $voltar }}">
@endif

<fieldset>
    <legend>Para quem</legend>
    <div @class(['campo', 'com-erro' => $errors->has('idoso_id')])>
        <label for="campo-idoso_id">Idoso</label>
        <select id="campo-idoso_id" name="idoso_id" required>
            <option value="">Escolha</option>
            @foreach ($idosos as $idoso)
                <option value="{{ $idoso->id }}" @selected((string) old('idoso_id', $cuidado->idoso_id) === (string) $idoso->id)>
                    {{ $idoso->nome }} ({{ $idoso->idade }} anos)
                </option>
            @endforeach
        </select>
        @error('idoso_id') <span class="erro">{{ $message }}</span> @enderror
    </div>
</fieldset>

<fieldset>
    <legend>Tipo de cuidado</legend>
    <div class="escolha-tipo">
        @foreach (\App\Enums\TipoCuidado::cases() as $tipo)
            <div class="tipo-{{ $tipo->value }}">
                <input type="radio" id="tipo-{{ $tipo->value }}" name="tipo" value="{{ $tipo->value }}"
                       @checked($tipoAtual === $tipo->value) required>
                <label for="tipo-{{ $tipo->value }}">
                    {{ $tipo->label() }}
                    <small>{{ $tipo->dica() }}</small>
                </label>
            </div>
        @endforeach
    </div>
    @error('tipo') <span class="erro">{{ $message }}</span> @enderror
</fieldset>

<fieldset class="tipo-medicacao" data-mostrar-para="medicacao">
    <legend>Medicação</legend>
    <div class="bloco-condicional">
        <div class="grade">
            <x-campo nome="medicamento" rotulo="Medicamento" :valor="$cuidado->medicamento" maxlength="150"
                     ajuda="Obrigatório quando o tipo é medicação" />
            <x-campo nome="dosagem" rotulo="Dose" :valor="$cuidado->dosagem" :opcional="true" maxlength="100"
                     ajuda="Ex.: 50 mg, 1 comprimido, 10 gotas" />
        </div>
    </div>
</fieldset>

<fieldset class="tipo-sinais_vitais" data-mostrar-para="sinais_vitais consulta">
    <legend>Sinais vitais</legend>
    <p class="explicacao">Preencha só o que foi medido.</p>
    <div class="bloco-condicional">
        <div class="grade grade-3 manter">
            <x-campo nome="pressao_sistolica" rotulo="Pressão máxima" tipo="number" unidade="mmHg" :valor="$cuidado->pressao_sistolica"
                     inputmode="numeric" min="50" max="260" ajuda="Sistólica. Em 12 por 8, é 120" />
            <x-campo nome="pressao_diastolica" rotulo="Pressão mínima" tipo="number" unidade="mmHg" :valor="$cuidado->pressao_diastolica"
                     inputmode="numeric" min="30" max="160" ajuda="Diastólica. Em 12 por 8, é 80" />
            <x-campo nome="frequencia_cardiaca" rotulo="Batimentos" tipo="number" unidade="bpm" :valor="$cuidado->frequencia_cardiaca"
                     inputmode="numeric" min="20" max="250" />
            <x-campo nome="temperatura" rotulo="Temperatura" unidade="°C"
                     :valor="$cuidado->temperatura !== null ? number_format((float) $cuidado->temperatura, 1, ',', '') : null"
                     inputmode="decimal" maxlength="5" placeholder="36,5" />
            <x-campo nome="glicemia" rotulo="Glicemia" tipo="number" unidade="mg/dL" :valor="$cuidado->glicemia"
                     inputmode="numeric" min="20" max="600" />
            <x-campo nome="saturacao" rotulo="Saturação" tipo="number" unidade="%" :valor="$cuidado->saturacao"
                     inputmode="numeric" min="50" max="100" />
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>O que foi feito</legend>
    <x-campo nome="descricao" rotulo="Descrição" tipo="textarea" :valor="$cuidado->descricao" required maxlength="2000"
             ajuda="Ex.: Comeu metade do almoço e recusou a salada." />
    <x-campo nome="observacoes" rotulo="Observações" tipo="textarea" :valor="$cuidado->observacoes" :opcional="true"
             ajuda="Reações, intercorrências, recados para quem vem depois." />
</fieldset>

<fieldset>
    <legend>Quando e quem</legend>
    <div class="grade">
        <x-campo nome="data_hora" rotulo="Data e hora" tipo="datetime-local"
                 :valor="$cuidado->data_hora?->toDateTimeLocalString('minute')" required />
        <x-campo nome="responsavel" rotulo="Quem cuidou" :valor="$cuidado->responsavel" required maxlength="150"
                 ajuda="Quem fez, ou vai fazer, o cuidado" />
    </div>

    <div @class(['campo', 'com-erro' => $errors->has('status')]) role="radiogroup" aria-labelledby="rotulo-status">
        <span class="rotulo" id="rotulo-status">Situação</span>
        <div class="escolha-status">
            @foreach (\App\Enums\StatusCuidado::cases() as $status)
                <label>
                    <input type="radio" name="status" value="{{ $status->value }}" @checked($statusAtual === $status->value) required>
                    <span>
                        {{ $status->label() }}
                        <span class="ajuda">{{ $status->dica() }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('status') <span class="erro">{{ $message }}</span> @enderror
    </div>
</fieldset>

@push('scripts')
    <script>
        // Mostra os campos de medicação ou de sinais vitais conforme o tipo escolhido.
        // Blocos que já têm valores (ou erros) continuam visíveis para nada ficar escondido.
        (function () {
            var blocos = document.querySelectorAll('[data-mostrar-para]');

            function temConteudo(bloco) {
                if (bloco.querySelector('.com-erro')) return true;
                return Array.prototype.some.call(bloco.querySelectorAll('input'), function (campo) {
                    return campo.value.trim() !== '';
                });
            }

            function atualizar() {
                var marcado = document.querySelector('input[name="tipo"]:checked');
                var tipo = marcado ? marcado.value : '';
                blocos.forEach(function (bloco) {
                    var tipos = bloco.getAttribute('data-mostrar-para').split(' ');
                    bloco.hidden = tipos.indexOf(tipo) === -1 && !temConteudo(bloco);
                });
            }

            document.querySelectorAll('input[name="tipo"]').forEach(function (radio) {
                radio.addEventListener('change', atualizar);
            });
            atualizar();
        })();
    </script>
@endpush
