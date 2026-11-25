@props(['pharmacie', 'distance' => null])
@php
    $a = $pharmacie->annuaire;
    $tels = $a?->telephones ?? [];
    $adresse = trim((string) ($a?->ADRESSE ?: $a?->LOCALISATION));
    $itineraire = $a?->a_geoloc
        ? 'https://www.google.com/maps/dir/?api=1&destination='.$a->LATITUDE.','.$a->LONGITUDE
        : ($a?->GEOLOCALISATION && str_starts_with($a->GEOLOCALISATION, 'http') ? $a->GEOLOCALISATION : null);
@endphp
<li {{ $attributes->merge(['class' => 'card flex flex-col gap-3 p-4 sm:flex-row sm:items-center']) }}>
    <div class="flex min-w-0 flex-1 gap-3">
        <span class="mt-0.5 grid size-10 shrink-0 place-items-center rounded-lg bg-garde-wash text-garde"><x-icon name="cross" class="size-5" /></span>
        <div class="min-w-0">
            <h3 class="font-sans text-[15px] font-semibold leading-snug">
                @if ($pharmacie->LIEN)
                    <a href="{{ route('annuaire.fiche', $pharmacie->LIEN) }}" class="hover:text-garde">{{ $pharmacie->TITRE }}</a>
                @else
                    {{ $pharmacie->TITRE }}
                @endif
            </h3>
            <p class="mt-0.5 text-sm text-muted">
                @if ($distance !== null)<span class="font-medium text-garde">{{ number_format($distance, 1, ',', ' ') }} km</span> · @endif
                {{ $adresse ?: ($pharmacie->commune?->DESIGNATION ?? '') }}
            </p>
        </div>
    </div>
    <div class="flex flex-wrap gap-2 sm:justify-end">
        @foreach (array_slice($tels, 0, 2) as $tel)
            <a href="{{ tel_link($tel) }}" onclick="pratikTrack('{{ route('stats.appel', $pharmacie->ID) }}')" class="btn-line btn-sm"><x-icon name="phone" class="size-3.5" /> {{ $tel }}</a>
        @endforeach
        @if ($itineraire)
            <a href="{{ $itineraire }}" target="_blank" rel="noopener" class="btn-garde btn-sm"><x-icon name="navigation" class="size-3.5" /> Itinéraire</a>
        @endif
    </div>
</li>
