@extends('layouts.site')

@section('title', 'Pharmacies de garde à Abidjan et en Côte d\'Ivoire')
@section('description', 'Liste des pharmacies de garde '.($periodeGarde ? $periodeGarde->libelle : 'de la semaine').' : Abidjan commune par commune et villes de l\'intérieur. Trouvez la plus proche de vous.')

@section('content')
    <x-page-header title="Pharmacies de garde" eyebrow="Semaine en cours" :breadcrumbs="['Accueil' => route('home'), 'Pharmacies de garde' => null]">
        @if ($periodeGarde)
            Tour de garde <strong class="font-semibold text-ink">{{ $periodeGarde->libelle }}</strong>.
        @endif
        {{ $total }} pharmacies de garde dans {{ $abidjan->count() + $interieur->count() }} villes et communes.
    </x-page-header>

    <div class="container-page space-y-12 py-10">
        @include('site.pharmacies._autour')

        @if ($abidjan->isEmpty() && $interieur->isEmpty())
            <x-empty icon="cross" title="La liste de cette semaine n'est pas encore publiée">
                Elle est mise en ligne chaque samedi matin. En cas d'urgence, appelez le 185 (SAMU).
            </x-empty>
        @endif

        @foreach (['Abidjan' => $abidjan, 'Intérieur du pays' => $interieur] as $zone => $communes)
            @if ($communes->isNotEmpty())
                <section>
                    <div class="flex items-baseline justify-between gap-4 border-b border-line pb-3">
                        <h2 class="section-title">{{ $zone }}</h2>
                        <span class="text-sm text-muted">{{ $communes->sum('total') }} pharmacies</span>
                    </div>
                    <ul class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($communes as $c)
                            <li>
                                <a href="{{ route('pharmacies.commune', $c->LIEN) }}" class="card card-hover group flex items-center justify-between gap-3 px-4 py-3">
                                    <span class="font-medium">{{ $c->DESIGNATION }}</span>
                                    <span class="inline-flex items-center gap-2 text-sm text-muted">
                                        <span class="tag-garde">{{ $c->total }}</span>
                                        <x-icon name="chevron-right" class="size-4 text-ink/30 group-hover:text-garde" />
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endforeach

        <p class="flex items-start gap-2 text-sm text-muted">
            <x-icon name="info" class="mt-0.5 size-4 shrink-0" />
            Les gardes changent chaque samedi. Avant de vous déplacer la nuit, un appel permet de confirmer que la pharmacie est bien ouverte.
        </p>
    </div>
@endsection
