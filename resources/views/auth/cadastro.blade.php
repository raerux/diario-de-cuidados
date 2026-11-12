<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.app-celular')
    <title>Criar acesso | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible+Next:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <main class="tela-entrar">
        <div class="cartao-entrar">
            <p class="marca">
                <x-marca />
                {{ config('app.name') }}
            </p>

            <h1>Criar seu acesso</h1>
            <p class="cartao-explicacao">Cadastre-se para registrar e acompanhar os cuidados do dia a dia.</p>

            <form method="POST" action="{{ route('cadastro.store') }}" novalidate>
                @csrf

                <x-campo nome="name" rotulo="Seu nome" :valor="null" required maxlength="150" autocomplete="name" autofocus />
                <x-campo nome="email" rotulo="E-mail" tipo="email" :valor="null" required autocomplete="username"
                         ajuda="Você vai usar para entrar no sistema" />
                <x-campo nome="password" rotulo="Senha" tipo="password" :valor="null" required autocomplete="new-password"
                         ajuda="Pelo menos 8 caracteres" />
                <x-campo nome="password_confirmation" rotulo="Repita a senha" tipo="password" :valor="null" required autocomplete="new-password" />

                <button type="submit" class="botao botao-principal">Criar acesso</button>
            </form>

            <p class="troca-acesso">Já tem acesso? <a href="{{ route('login') }}">Entrar</a></p>
        </div>
    </main>
</body>
</html>
