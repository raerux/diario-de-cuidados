<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar | {{ config('app.name') }}</title>
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

            <h1>Entrar</h1>

            <form method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf

                <div class="campo @error('email') com-erro @enderror">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           autocomplete="username" autofocus required>
                    @error('email') <span class="erro">{{ $message }}</span> @enderror
                </div>

                <div class="campo @error('password') com-erro @enderror">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                    @error('password') <span class="erro">{{ $message }}</span> @enderror
                </div>

                <div class="campo">
                    <label class="caixa-marcar">
                        <input type="checkbox" name="lembrar" value="1" @checked(old('lembrar'))>
                        <span>Manter conectado neste aparelho</span>
                    </label>
                </div>

                <button type="submit" class="botao botao-principal">Entrar</button>
            </form>
        </div>
    </main>
</body>
</html>
