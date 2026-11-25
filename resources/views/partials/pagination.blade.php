@if ($paginator->hasPages())
    <nav class="mt-10 flex items-center justify-between gap-4 text-sm" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="btn-line pointer-events-none opacity-40"><x-icon name="arrow-left" class="size-4" /> Précédent</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-line"><x-icon name="arrow-left" class="size-4" /> Précédent</a>
        @endif

        @if (method_exists($paginator, 'lastPage'))
            <span class="text-muted">Page <strong class="text-ink">{{ $paginator->currentPage() }}</strong> sur {{ $paginator->lastPage() }}</span>
        @else
            <span class="text-muted">Page {{ $paginator->currentPage() }}</span>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-line">Suivant <x-icon name="arrow-right" class="size-4" /></a>
        @else
            <span class="btn-line pointer-events-none opacity-40">Suivant <x-icon name="arrow-right" class="size-4" /></span>
        @endif
    </nav>
@endif
