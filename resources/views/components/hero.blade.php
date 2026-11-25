@props(['hero', 'tone' => 'site'])
@php
    // Couleur du mot mis en avant = couleur du logo (ambre pour Pratik, jaune toit pour Immobilier).
    $accent = $tone === 'immo' ? 'text-roof' : 'text-amber';
    $voile = $hero['voile'];
    $position = ['top' => 'object-top', 'center' => 'object-center', 'bottom' => 'object-bottom'][$hero['cadrage'] ?? 'center'];
    $degrade = sprintf(
        'linear-gradient(90deg, rgba(20,32,43,%.2f) 0%%, rgba(20,32,43,%.2f) 55%%, rgba(20,32,43,%.2f) 100%%)',
        min(0.95, $voile + 0.15), $voile, $voile * 0.55,
    );
@endphp

@if ($hero['affichage'] === 'banniere')
    {{-- Bannière déjà composée (texte intégré) : affichée telle quelle --}}
    <section {{ $attributes->merge(['class' => 'bg-white']) }}>
        <h1 class="sr-only">{{ trim($hero['titre'].' '.$hero['titre_accent']) }}</h1>
        <picture>
            @if ($hero['image_mobile_url'])
                <source media="(max-width: 767px)" srcset="{{ $hero['image_mobile_url'] }}">
            @endif
            <img src="{{ $hero['image_url'] }}" alt="" fetchpriority="high" class="block max-h-[560px] w-full object-cover">
        </picture>
        <div class="border-b border-line">
            <div class="container-page grid gap-8 py-8 lg:grid-cols-[1.35fr_1fr] lg:items-start">
                <div>{{ $slot }}</div>
                @isset($aside)<div>{{ $aside }}</div>@endisset
            </div>
        </div>
        @isset($bas)
            <div class="bg-ink text-white">{{ $bas }}</div>
        @endisset
    </section>
@else
    {{-- Photo de fond + voile + titre --}}
    <section {{ $attributes->merge(['class' => 'relative isolate overflow-hidden bg-ink text-white']) }}>
        <picture>
            @if ($hero['image_mobile_url'])
                <source media="(max-width: 767px)" srcset="{{ $hero['image_mobile_url'] }}">
            @endif
            <img src="{{ $hero['image_url'] }}" alt="" fetchpriority="high" class="absolute inset-0 -z-20 size-full object-cover {{ $position }}">
        </picture>
        <div class="absolute inset-0 -z-10" style="background: {{ $degrade }}"></div>

        <div class="container-page grid gap-10 py-12 lg:grid-cols-[1.35fr_1fr] lg:items-center lg:py-20">
            <div>
                @if ($hero['surtitre'])
                    <p class="text-xs font-semibold tracking-[0.14em] uppercase {{ $accent }}">{{ $hero['surtitre'] }}</p>
                @endif
                <h1 class="mt-3 max-w-2xl text-4xl leading-[1.1] font-semibold !text-white sm:text-5xl">
                    {{ $hero['titre'] }}
                    @if ($hero['titre_accent'])
                        <em class="font-medium {{ $accent }}">{{ $hero['titre_accent'] }}</em>
                    @endif
                </h1>
                @if ($hero['texte'])
                    <p class="mt-4 max-w-xl text-lg leading-relaxed text-white/85">{{ $hero['texte'] }}</p>
                @endif
                <div class="mt-8">{{ $slot }}</div>
            </div>
            @isset($aside)<div>{{ $aside }}</div>@endisset
        </div>

        @isset($bas)
            <div class="border-t border-white/15 bg-ink/40 backdrop-blur-sm">{{ $bas }}</div>
        @endisset
    </section>
@endif
