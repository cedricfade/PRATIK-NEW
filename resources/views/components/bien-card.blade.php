@props(['bien'])
@php($e = $bien->entreprise)
<article {{ $attributes->merge(['class' => 'card card-hover group relative flex flex-col overflow-hidden']) }}>
    <div class="relative aspect-[4/3] overflow-hidden bg-sand" x-data="{ ko: false }">
        <img src="{{ $bien->image_url }}" alt="{{ $e?->TITRE }}" loading="lazy" x-show="!ko" x-on:error="ko = true"
             class="size-full object-cover transition duration-500 group-hover:scale-[1.03]">
        <div x-show="ko" x-cloak class="grid size-full place-items-center text-ink/30"><x-icon name="home" class="size-10" /></div>
        <span @class([
            'absolute top-3 left-3 rounded px-2 py-1 text-[11px] font-bold tracking-wide uppercase',
            'bg-white text-ink' => $bien->est_location,
            'bg-roof text-ink' => ! $bien->est_location,
        ])>{{ $bien->est_location ? 'À louer' : 'À vendre' }}</span>
        @if (strtoupper((string) $bien->LUXE) === 'OUI')
            <span class="absolute top-3 right-3 rounded bg-ink px-2 py-1 text-[11px] font-semibold text-roof">Prestige</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-4">
        <p class="font-serif text-lg font-semibold text-ink">{{ $bien->prix_affiche }}</p>
        <h3 class="mt-1 line-clamp-2 text-sm font-medium leading-snug text-ink/85">
            <a href="{{ $e?->LIEN ? route('immo.bien', $e->LIEN) : '#' }}" class="after:absolute after:inset-0">{{ $e?->TITRE }}</a>
        </h3>
        <p class="mt-1.5 inline-flex items-center gap-1 text-[13px] text-muted">
            <x-icon name="map-pin" class="size-3.5" />
            {{ $e?->commune?->DESIGNATION ?? 'Côte d\'Ivoire' }}@if ($bien->categorie) · {{ $bien->categorie->DESIGNATION }}@endif
        </p>
        <div class="mt-auto flex gap-4 border-t border-line pt-3 text-[13px] text-ink/70 [&:not(:has(span))]:hidden">
            @if ($bien->CHAMBRE)<span class="inline-flex items-center gap-1"><x-icon name="bed" class="size-4" /> {{ $bien->CHAMBRE }} ch.</span>@endif
            @if ($bien->SALLE_BAIN)<span class="inline-flex items-center gap-1"><x-icon name="bath" class="size-4" /> {{ $bien->SALLE_BAIN }} sdb</span>@endif
            @if ($bien->SURFACE)<span class="inline-flex items-center gap-1"><x-icon name="ruler" class="size-4" /> {{ $bien->SURFACE }} m²</span>@endif
        </div>
    </div>
</article>
