@extends('layouts.site')

@section('description', "Pratik, l'annuaire pratique de Côte d'Ivoire : ".number_format($chiffres['entreprises'], 0, ',', ' ')." entreprises, les pharmacies de garde de la semaine, la revue de presse du jour, les papiers administratifs et l'immobilier.")

@php
    $raccourcis = [
        ['Pharmacies', 'sante/pharmacies'],
        ['Hôtels', 'sorties/hotels'],
        ['Restaurants', 'loisirs/restaurants'],
        ['Cliniques & médecins', 'sante/medecine-generale'],
        ['Assurances', 'finances-assurances/assurances'],
        ['Notaires', 'activites-juridiques/notaires'],
        ['Garages & voitures', 'auto-moto/vente-de-voitures'],
        ['BTP', 'batiment-construction/batiment-construction'],
    ];
    $abidjan = $gardesParCommune->filter(fn ($c) => in_array((int) $c->ID, config('pratik.communes_abidjan'), true));
@endphp

@section('content')
    {{-- 1. Hero (image de fond et textes réglables dans Admin › Apparence) + recherche + gardes --}}
    @php $surPhoto = $hero['affichage'] === 'photo'; @endphp
    <x-hero :hero="$hero">
        <form action="{{ route('recherche') }}" method="GET" class="flex flex-col gap-2 rounded-xl bg-white p-2 text-ink shadow-[var(--shadow-lift)] sm:flex-row" role="search">
            <label class="sr-only" for="q">Que cherchez-vous ?</label>
            <div class="flex flex-1 items-center gap-2 rounded-lg px-3">
                <x-icon name="search" class="size-5 text-muted" />
                <input id="q" name="q" type="search" required minlength="2" placeholder="Une entreprise, un métier, un sigle…" class="w-full border-0 bg-transparent py-3 text-[15px] focus:ring-0">
            </div>
            <label class="sr-only" for="commune">Commune</label>
            <select id="commune" name="commune" class="rounded-lg border-0 bg-sand/70 py-3 pr-9 text-sm text-ink/80 focus:ring-2 focus:ring-amber/40 sm:w-48">
                <option value="">Toutes les villes</option>
                @foreach ($communes as $c)
                    <option value="{{ $c->LIEN }}">{{ $c->DESIGNATION }}</option>
                @endforeach
            </select>
            <button class="btn-amber px-6 py-3">Rechercher</button>
        </form>

        <div class="mt-5 flex flex-wrap items-center gap-2 text-sm">
            <span @class(['text-white/75' => $surPhoto, 'text-muted' => ! $surPhoto])>Souvent cherché :</span>
            @foreach ($raccourcis as [$label, $chemin])
                <a href="{{ url('/annuaire/'.$chemin) }}" @class([
                    'rounded-full border px-3 py-1',
                    'border-white/25 bg-white/10 text-white hover:bg-white/20' => $surPhoto,
                    'border-line bg-white text-ink/80 hover:border-ink/30 hover:text-ink' => ! $surPhoto,
                ])>{{ $label }}</a>
            @endforeach
        </div>

        <x-slot:aside>
            <aside class="rounded-xl bg-white p-5 text-ink shadow-[var(--shadow-lift)] sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="flex items-center gap-2 text-sm font-semibold text-garde">
                            <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-garde/60"></span><span class="relative inline-flex size-2 rounded-full bg-garde"></span></span>
                            Pharmacies de garde
                        </p>
                        <p class="mt-1 text-sm text-ink/70">
                            @if ($periodeGarde)
                                Semaine {{ $periodeGarde->libelle }}
                            @else
                                Liste de la semaine en cours
                            @endif
                        </p>
                    </div>
                    <x-icon name="cross" class="size-7 text-garde" />
                </div>

                @if ($abidjan->isNotEmpty())
                    <ul class="mt-4 grid grid-cols-2 gap-x-4 text-sm">
                        @foreach ($abidjan->take(12) as $c)
                            <li>
                                <a href="{{ url('/pharmacies-de-garde/'.$c->LIEN) }}" class="flex items-center justify-between border-b border-line/70 py-2 hover:text-garde">
                                    <span>{{ $c->DESIGNATION }}</span>
                                    <span class="text-xs text-ink/50">{{ $c->total }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-5 text-sm text-ink/70">La liste de la semaine sera publiée très bientôt.</p>
                @endif

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('pharmacies.index') }}#autour-de-moi" class="btn-garde"><x-icon name="locate" class="size-4" /> Autour de moi</a>
                    <a href="{{ route('pharmacies.index') }}" class="btn-line">Toutes les villes</a>
                </div>
            </aside>
        </x-slot:aside>

        @if ($chiffresHero['afficher'])
            <x-slot:bas>
                <dl class="container-page grid grid-cols-2 gap-y-5 py-6 sm:grid-cols-4 sm:divide-x sm:divide-white/15">
                    @foreach ([
                        [number_format($chiffres['entreprises'], 0, ',', "\u{202F}"), 'entreprises référencées'],
                        [number_format($chiffres['biens'], 0, ',', "\u{202F}"), 'annonces immobilières'],
                        [number_format($chiffres['pharmacies'], 0, ',', "\u{202F}"), 'pharmacies recensées'],
                        [$chiffresHero['visiteurs'], $chiffresHero['visiteurs_libelle']],
                    ] as [$nombre, $libelle])
                        <div class="sm:px-6 sm:first:pl-0">
                            <dt class="sr-only">{{ $libelle }}</dt>
                            <dd class="font-serif text-3xl font-semibold text-amber">{{ $nombre }}</dd>
                            <dd class="mt-0.5 text-sm text-white/75">{{ $libelle }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-slot:bas>
        @endif
    </x-hero>

    {{-- 2. Services --}}
    <section class="container-page py-14">
        <div class="grid gap-px overflow-hidden rounded-xl border border-line bg-line sm:grid-cols-2 lg:grid-cols-3">
            @php
                $services = [
                    ['building', 'Annuaire des entreprises', 'Coordonnées, plans d\'accès et activités de plus de '.number_format(floor($chiffres['entreprises'] / 1000) * 1000, 0, ',', ' ').' entreprises.', route('annuaire.index'), false],
                    ['cross', 'Pharmacies de garde', 'La liste officielle de la semaine, par commune ou autour de vous.', route('pharmacies.index'), false],
                    ['newspaper', 'Revue de presse', 'Les unes des quotidiens ivoiriens chaque matin, et l\'actualité du web.', route('news.index'), false],
                    ['home', 'Pratik Immobilier', number_format($chiffres['biens'], 0, ',', ' ').' biens à vendre ou à louer, proposés par des professionnels.', route('immo.home'), true],
                    ['file-text', 'Papiers administratifs', 'Passeport, CNI, actes d\'état civil : les pièces à fournir et les bons guichets.', route('papiers.index'), false],
                    ['plane', 'Horaires des vols', 'Départs et arrivées à l\'aéroport Félix Houphouët-Boigny.', route('vols.index'), false],
                ];
            @endphp
            @foreach ($services as [$icone, $titre, $texte, $lien, $nouvelOnglet])
                <a href="{{ $lien }}" @if ($nouvelOnglet) target="_blank" rel="noopener" @endif class="group flex gap-4 bg-white p-6 hover:bg-paper">
                    <span class="grid size-11 shrink-0 place-items-center rounded-lg {{ $icone === 'home' ? 'bg-roof/20 text-roof-deep' : ($icone === 'cross' ? 'bg-garde-wash text-garde' : 'bg-sand text-ink') }}">
                        <x-icon :name="$icone" class="size-5" />
                    </span>
                    <span>
                        <span class="flex items-center gap-1.5 font-semibold text-ink">
                            {{ $titre }}
                            <x-icon :name="$nouvelOnglet ? 'external' : 'arrow-right'" class="size-3.5 opacity-0 transition group-hover:translate-x-0.5 group-hover:opacity-100" />
                        </span>
                        <span class="mt-1 block text-sm leading-6 text-muted">{{ $texte }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- 3. Les unes du jour --}}
    @if ($unes->isNotEmpty())
        <section class="border-y border-line bg-white py-12">
            <div class="container-page">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow">Revue de presse</p>
                        <h2 class="section-title mt-1">Les unes du {{ \Carbon\Carbon::parse($unes->first()->DATE_PARUTION)->locale('fr')->isoFormat('D MMMM') }}</h2>
                    </div>
                    <a href="{{ route('news.index') }}" class="link hidden text-sm font-medium sm:inline">Toute la presse du jour</a>
                </div>
                <div class="-mx-4 mt-6 flex snap-x gap-4 overflow-x-auto px-4 pb-3 sm:mx-0 sm:px-0">
                    @foreach ($unes as $une)
                        <a href="{{ $une->entreprise?->LIEN ? url('/news/'.$une->entreprise->LIEN) : route('news.index') }}" class="group w-36 shrink-0 snap-start sm:w-40">
                            <div class="aspect-[3/4] overflow-hidden rounded-md border border-line bg-sand shadow-sm">
                                <img src="{{ $une->image_url }}" alt="Une de {{ $une->source?->DESIGNATION ?? $une->titre }}" loading="lazy" class="size-full object-cover object-top transition group-hover:scale-[1.02]">
                            </div>
                            <p class="mt-2 truncate text-sm font-medium">{{ $une->source?->DESIGNATION ?? $une->titre }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 4. Entreprises partenaires --}}
    @if ($partenaires->isNotEmpty())
        <section class="container-page py-14">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Ils sont sur Pratik</p>
                    <h2 class="section-title mt-1">Entreprises partenaires</h2>
                </div>
                <a href="{{ route('souscription') }}" class="link hidden text-sm font-medium sm:inline">Rejoindre l'annuaire</a>
            </div>
            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($partenaires as $entreprise)
                    <x-entreprise-card :entreprise="$entreprise" :show-phone="false" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- 5. Immobilier --}}
    @if ($biens->isNotEmpty())
        <section class="bg-ink py-14 text-white">
            <div class="container-page">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow !text-roof">Pratik Immobilier</p>
                        <h2 class="mt-1 font-serif text-2xl font-semibold text-white sm:text-3xl">Les dernières annonces</h2>
                    </div>
                    <a href="{{ route('immo.home') }}" target="_blank" rel="noopener" class="btn-roof">Voir toutes les annonces <x-icon name="external" class="size-4" /></a>
                </div>
                <div class="mt-8 grid gap-5 text-ink sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($biens as $bien)
                        <x-bien-card :bien="$bien" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 6. Professionnels + application --}}
    <section class="container-page grid gap-6 py-14 lg:grid-cols-2">
        <div class="card flex flex-col justify-between gap-6 p-7">
            <div>
                <p class="eyebrow">Professionnels</p>
                <h2 class="mt-2 text-2xl font-semibold">Votre entreprise mérite d'être trouvée.</h2>
                <p class="mt-3 text-ink/75">Fiche complète avec logo, photos, horaires et plan d'accès, mise en avant dans votre catégorie et statistiques de consultation.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('souscription') }}" class="btn-ink">Inscrire mon entreprise</a>
                <a href="{{ route('contact') }}" class="btn-line">Parler à un conseiller</a>
            </div>
        </div>
        <div class="card flex flex-col justify-between gap-6 bg-sand/60 p-7">
            <div>
                <p class="eyebrow">Application mobile</p>
                <h2 class="mt-2 text-2xl font-semibold">Pratik dans votre poche.</h2>
                <p class="mt-3 text-ink/75">La pharmacie de garde la plus proche en deux gestes, l'annuaire et les annonces immobilières, même hors de chez vous.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ config('pratik.apps.android') }}" target="_blank" rel="noopener"><img src="{{ asset('brand/google-play.svg') }}" alt="Disponible sur Google Play" class="h-11 w-auto"></a>
                <a href="{{ config('pratik.apps.ios') }}" target="_blank" rel="noopener"><img src="{{ asset('brand/app-store.svg') }}" alt="Télécharger dans l'App Store" class="h-11 w-auto"></a>
            </div>
        </div>
    </section>

    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'Pratik',
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => route('recherche').'?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
