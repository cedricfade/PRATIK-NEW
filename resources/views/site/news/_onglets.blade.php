<nav class="flex gap-1 overflow-x-auto border-b border-line" aria-label="Rubriques news">
    @foreach (['news.index' => 'Unes des journaux', 'news.web' => 'Actualité du web', 'news.videos' => 'Vidéos'] as $route => $label)
        @php($actif = request()->routeIs($route) || ($route === 'news.index' && request()->routeIs('news.archives', 'news.une')) || ($route === 'news.web' && request()->routeIs('news.article')))
        <a href="{{ route($route) }}" @class([
            'whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium',
            'border-amber text-ink' => $actif,
            'border-transparent text-ink/60 hover:text-ink' => ! $actif,
        ])>{{ $label }}</a>
    @endforeach
</nav>
