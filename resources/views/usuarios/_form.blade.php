@if ($errors->any())
    <div class="aviso aviso-erro" role="alert">
        Confira os campos destacados antes de salvar.
    </div>
@endif

<fieldset>
    <legend>Dados de acesso</legend>
    <x-campo nome="name" rotulo="Nome" :valor="$usuario->name" required maxlength="150" autocomplete="off" />
    <x-campo nome="email" rotulo="E-mail" tipo="email" :valor="$usuario->email" required autocomplete="off"
             ajuda="Usado para entrar no sistema" />
</fieldset>

<fieldset>
    <legend>Senha</legend>
    @if ($usuario->exists)
        <p class="explicacao">Deixe em branco para manter a senha atual.</p>
    @endif
    <div class="grade">
        <x-campo nome="password" rotulo="Senha" tipo="password" :valor="null" autocomplete="new-password"
                 ajuda="Pelo menos 8 caracteres" />
        <x-campo nome="password_confirmation" rotulo="Repita a senha" tipo="password" :valor="null" autocomplete="new-password" />
    </div>
</fieldset>
