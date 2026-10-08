<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Hoje') | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible+Next:ital,wght@0,400;0,600;0,700;1,400&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="topo">
        <div class="topo-interno">
            <a href="{{ route('dashboard') }}" class="marca">
                <x-marca />
                {{ config('app.name') }}
            </a>

            <nav class="menu" aria-label="Principal">
                <a href="{{ route('dashboard') }}" @if (request()->routeIs('dashboard')) aria-current="page" @endif>Hoje</a>
                <a href="{{ route('idosos.index') }}" @if (request()->routeIs('idosos.*')) aria-current="page" @endif>Idosos</a>
                <a href="{{ route('cuidados.index') }}" @if (request()->routeIs('cuidados.*')) aria-current="page" @endif>Histórico</a>
                <a href="{{ route('usuarios.index') }}" @if (request()->routeIs('usuarios.*')) aria-current="page" @endif>Acessos</a>
            </nav>

            <div class="conta">
                <span class="nome-usuario">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sair</button>
                </form>
            </div>
        </div>
    </header>

    <main class="pagina">
        @if (session('sucesso'))
            <div class="aviso aviso-sucesso" role="status">{{ session('sucesso') }}</div>
        @endif

        @if (session('erro'))
            <div class="aviso aviso-erro" role="alert">{{ session('erro') }}</div>
        @endif

        @yield('conteudo')
    </main>

    <script>
        // Pede confirmação em formulários marcados com data-confirmar (exclusões).
        document.addEventListener('submit', function (evento) {
            var mensagem = evento.target.getAttribute('data-confirmar');
            if (mensagem && !window.confirm(mensagem)) {
                evento.preventDefault();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
