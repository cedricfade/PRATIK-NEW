@props(['entreprise', 'showPhone' => true])
@php
    $a = $entreprise->annuaire;
    $tel = $a?->telephones[0] ?? null;
@endphp
<article {{ $attributes->merge(['class' => 'card card-hover group relative flex gap-4 p-4']) }}>
    <x-logo-tile :src="$a?->logo_url" :name="$entreprise->TITRE" />
    <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-sans text-[15px] font-semibold leading-snug text-ink">
                <a href="{{ route('annuaire.fiche', $entreprise->LIEN) }}" class="after:absolute after:inset-0">{{ $entreprise->TITRE }}</a>
            </h3>
            @if ($a?->est_client)
                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber px-2 py-0.5 text-[11px] font-bold tracking-wide text-ink" title="Entreprise partenaire de Pratik"><x-icon name="crown" class="size-3.5" /> TOP 12</span>
            @endif
        </div>
        @if ($entreprise->ACTIVITE)
            <p class="mt-0.5 line-clamp-1 text-sm text-muted">{{ $entreprise->ACTIVITE }}</p>
        @endif
        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[13px] text-ink/70">
            @if ($entreprise->commune)
                <span class="inline-flex items-center gap-1"><x-icon name="map-pin" class="size-3.5" /> {{ $entreprise->commune->DESIGNATION }}</span>
            @endif
            @if ($showPhone && $tel)
                <a href="{{ tel_link($tel) }}" class="relative z-10 inline-flex items-center gap-1 hover:text-ink"><x-icon name="phone" class="size-3.5" /> {{ $tel }}</a>
            @endif
        </div>
    </div>
</article>
