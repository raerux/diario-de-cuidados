@if ($errors->any())
    <div class="aviso aviso-erro" role="alert">
        Confira os campos destacados antes de salvar.
    </div>
@endif

<fieldset>
    <legend>Identificação</legend>
    <div class="grade">
        <x-campo nome="nome" rotulo="Nome completo" :valor="$idoso->nome" :largo="true" required maxlength="150" autocomplete="off" />
        <x-campo nome="data_nascimento" rotulo="Data de nascimento" tipo="date" :valor="$idoso->data_nascimento?->format('Y-m-d')" required />

        <div class="campo @error('sexo') com-erro @enderror">
            <label for="campo-sexo">Sexo <span class="opcional">(opcional)</span></label>
            <select id="campo-sexo" name="sexo">
                <option value="">Não informado</option>
                @foreach (\App\Enums\Sexo::opcoes() as $valor => $rotulo)
                    <option value="{{ $valor }}" @selected(old('sexo', $idoso->sexo?->value) === $valor)>{{ $rotulo }}</option>
                @endforeach
            </select>
            @error('sexo') <span class="erro">{{ $message }}</span> @enderror
        </div>

        <x-campo nome="cpf" rotulo="CPF" :valor="$idoso->cpf_formatado" :opcional="true" inputmode="numeric" maxlength="14" placeholder="000.000.000-00" />

        <div class="campo @error('tipo_sanguineo') com-erro @enderror">
            <label for="campo-tipo_sanguineo">Tipo sanguíneo <span class="opcional">(opcional)</span></label>
            <select id="campo-tipo_sanguineo" name="tipo_sanguineo">
                <option value="">Não sei</option>
                @foreach (\App\Models\Idoso::TIPOS_SANGUINEOS as $tipo)
                    <option value="{{ $tipo }}" @selected(old('tipo_sanguineo', $idoso->tipo_sanguineo) === $tipo)>{{ $tipo }}</option>
                @endforeach
            </select>
            @error('tipo_sanguineo') <span class="erro">{{ $message }}</span> @enderror
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>Contato</legend>
    <div class="grade">
        <x-campo nome="telefone" rotulo="Telefone do idoso" tipo="tel" :valor="$idoso->telefone" :opcional="true" maxlength="20" />
        <x-campo nome="endereco" rotulo="Endereço" :valor="$idoso->endereco" :opcional="true" maxlength="255" />
        <x-campo nome="contato_emergencia_nome" rotulo="Contato de emergência" :valor="$idoso->contato_emergencia_nome"
                 :opcional="true" maxlength="150" ajuda="Nome e parentesco, por exemplo: Raimunda (filha)" />
        <x-campo nome="contato_emergencia_telefone" rotulo="Telefone de emergência" tipo="tel" :valor="$idoso->contato_emergencia_telefone"
                 :opcional="true" maxlength="20" />
    </div>
</fieldset>

<fieldset>
    <legend>Saúde</legend>
    <p class="explicacao">Escreva um item por linha. Essas informações aparecem na ficha do idoso para quem for cuidar.</p>
    <x-campo nome="alergias" rotulo="Alergias" tipo="textarea" :valor="$idoso->alergias" :opcional="true"
             ajuda="Remédios, alimentos ou materiais. Aparece em destaque na ficha." />
    <x-campo nome="condicoes_saude" rotulo="Condições de saúde" tipo="textarea" :valor="$idoso->condicoes_saude" :opcional="true"
             ajuda="Doenças, limitações e diagnósticos." />
    <x-campo nome="medicamentos_continuos" rotulo="Medicamentos de uso contínuo" tipo="textarea" :valor="$idoso->medicamentos_continuos"
             :opcional="true" ajuda="Nome, dose e horários. Ex.: Losartana 50 mg - 8h e 20h" />
    <x-campo nome="observacoes" rotulo="Observações" tipo="textarea" :valor="$idoso->observacoes" :opcional="true"
             ajuda="Preferências, rotina, o que acalma, o que evitar." />
</fieldset>

<fieldset>
    <legend>Acompanhamento</legend>
    <label class="caixa-marcar">
        <input type="hidden" name="ativo" value="0">
        <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $idoso->ativo))>
        <span>
            Em acompanhamento
            <span class="ajuda">Desmarque quando a pessoa deixar de ser acompanhada. O histórico continua guardado.</span>
        </span>
    </label>
</fieldset>
