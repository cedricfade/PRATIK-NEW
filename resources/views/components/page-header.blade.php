@props(['title', 'eyebrow' => null, 'breadcrumbs' => [], 'tone' => 'site'])
<section {{ $attributes->merge(['class' => 'border-b border-line '.($tone === 'immo' ? 'bg-white' : 'bg-sand/60')]) }}>
    <div class="container-page py-8 sm:py-10">
        @if ($breadcrumbs)
            <nav class="mb-3 flex flex-wrap items-center gap-1 text-[13px] text-muted" aria-label="Fil d'Ariane">
                @foreach ($breadcrumbs as $label => $url)
                    @if (! $loop->first)<x-icon name="chevron-right" class="size-3.5" />@endif
                    @if ($url)<a href="{{ $url }}" class="hover:text-ink">{{ $label }}</a>@else<span class="text-ink/80">{{ $label }}</span>@endif
                @endforeach
            </nav>
            <script type="application/ld+json">{!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect($breadcrumbs)->keys()->map(fn ($label, $i) => array_filter([
                    '@type' => 'ListItem', 'position' => $i + 1, 'name' => $label, 'item' => array_values($breadcrumbs)[$i] ?: null,
                ]))->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endif
        @if ($eyebrow)<p class="eyebrow {{ $tone === 'immo' ? '!text-roof-deep' : '' }}">{{ $eyebrow }}</p>@endif
        <h1 class="mt-1 text-3xl font-semibold sm:text-4xl">{{ $title }}</h1>
        @if ($slot->isNotEmpty())
            <div class="mt-3 max-w-3xl text-ink/75">{{ $slot }}</div>
        @endif
        @isset($actions)<div class="mt-5">{{ $actions }}</div>@endisset
    </div>
</section>
