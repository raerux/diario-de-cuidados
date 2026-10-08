@if ($paginator->hasPages())
    <nav class="paginacao" aria-label="Páginas">
        @if ($paginator->onFirstPage())
            <span class="desativado" aria-hidden="true">Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
        @endif

        @isset($elements)
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="reticencias" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $pagina => $url)
                        @if ($pagina == $paginator->currentPage())
                            <span aria-current="page">{{ $pagina }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Página {{ $pagina }}">{{ $pagina }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endisset

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Próxima</a>
        @else
            <span class="desativado" aria-hidden="true">Próxima</span>
        @endif
    </nav>
@endif
