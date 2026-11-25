@extends('layouts.immo')

@php
    $images = collect([$bien->image_url])->merge($bien->photos->map->url)->filter()->unique()->values();
    $commune = $fiche->commune?->DESIGNATION;
    $resume = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $bien->DESCRIPTION))), 155);
    $prix = $bien->prix_affiche;
    if ($bien->est_location && strtoupper((string) $bien->PERIODE) === 'JOUR') {
        $prix = str_replace('/ mois', '/ jour', $prix);
    }
    $carac = array_filter([
        ['ruler', 'Surface', $bien->SURFACE ? $bien->SURFACE.' m²' : null],
        ['layers', 'Pièces', $bien->PIECES ?: null],
        ['bed', 'Chambres', $bien->CHAMBRE ?: null],
        ['bath', 'Salles de bain', $bien->SALLE_BAIN ?: null],
        ['sofa', 'Salons', $bien->SALONS ?: null],
        ['tag', 'État', $bien->ETAT_BIEN ? ucfirst(strtolower($bien->ETAT_BIEN)) : null],
        ['banknote', 'Prix au m²', (int) preg_replace('/\D/', '', (string) $bien->PRIX_M2) > 0 ? fcfa($bien->PRIX_M2) : null],
        ['calendar', 'Âge du bâtiment', $bien->AGE ? $bien->AGE.' an'.($bien->AGE > 1 ? 's' : '') : null],
    ], fn ($c) => $c[2] !== null);
    // Titres saisis en majuscules : mis en casse normale, en gardant la commune capitalisée.
    $titreLisible = $fiche->TITRE === mb_strtoupper($fiche->TITRE)
        ? \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($fiche->TITRE))
        : $fiche->TITRE;
    if ($commune) {
        $titreLisible = preg_replace('/\b'.preg_quote(mb_strtolower($commune), '/').'\b/u', $commune, $titreLisible);
    }
    $adresse = trim((string) $bien->ADRESSE);
    $adresse = $adresse === '' ? ($commune ?? 'Côte d\'Ivoire')
        : \Illuminate\Support\Str::limit($adresse, 120).($commune && ! \Illuminate\Support\Str::contains(mb_strtolower($adresse), mb_strtolower($commune)) ? ', '.$commune : '');
    $suivi = fn (string $type) => Route::has('stats.'.$type) ? "pratikTrack('".route('stats.'.$type, $entrepriseStats)."')" : '';
@endphp

@section('title', $fiche->TITRE.($commune ? ' — '.$commune : ''))
@section('description', ($bien->est_location ? 'À louer' : 'À vendre').' : '.$fiche->TITRE.', '.$prix.($commune ? ', '.$commune : '').'. '.$resume)
@section('og_type', 'article')
@section('og_image', $bien->image_url)
@section('canonical', route('immo.bien', $fiche->LIEN))
@if ($bien->VALIDATION !== 'OUI')
    @section('noindex', true)
@endif

@section('content')
    @if ($estProprietaire)
        <div class="border-b border-roof/40 bg-roof/15">
            <div class="container-page flex flex-wrap items-center justify-between gap-3 py-3 text-sm">
                <p class="flex items-center gap-2">
                    <x-immo.statut :validation="$bien->VALIDATION" />
                    @if ($bien->VALIDATION !== 'OUI')
                        Aperçu : cette annonce n'est pas encore visible du public.
                    @else
                        Vous consultez votre propre annonce (vos visites ne sont pas comptées).
                    @endif
                </p>
                <a href="{{ route('immo.pro.biens.edit', $bien->ID) }}" class="btn-line btn-sm"><x-icon name="pencil" class="size-3.5" /> Modifier l'annonce</a>
            </div>
        </div>
    @endif

    <div class="container-page py-6">
        <nav class="flex flex-wrap items-center gap-1 text-[13px] text-muted" aria-label="Fil d'Ariane">
            <a href="{{ route('immo.home') }}" class="hover:text-ink">Immobilier</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <a href="{{ $bien->est_location ? route('immo.locations') : route('immo.achats') }}" class="hover:text-ink">{{ $bien->est_location ? 'Location' : 'Vente' }}</a>
            @if ($commune)
                <x-icon name="chevron-right" class="size-3.5" />
                <a href="{{ route($bien->est_location ? 'immo.locations' : 'immo.achats', ['commune' => $fiche->COMMUNE_ID]) }}" class="hover:text-ink">{{ $commune }}</a>
            @endif
        </nav>

        {{-- Galerie --}}
        <div class="mt-4" x-data="gallery(@js($images))">
            <div @class(['grid gap-2 overflow-hidden rounded-xl', 'sm:grid-cols-4 sm:grid-rows-2 sm:h-[460px]' => $images->count() >= 3, 'sm:h-[460px]' => $images->count() < 3])>
                @foreach ($images->take(5) as $i => $src)
                    <button type="button" @click="show({{ $i }})" @class([
                        'group relative block overflow-hidden bg-sand',
                        'aspect-[4/3] sm:aspect-auto sm:col-span-2 sm:row-span-2' => $i === 0,
                        'hidden sm:block' => $i > 0,
                        'sm:col-span-2' => $i > 0 && $images->count() === 3,
                    ])>
                        <img src="{{ $src }}" alt="{{ $fiche->TITRE }} — photo {{ $i + 1 }}" @if ($i > 0) loading="lazy" @endif class="size-full object-cover transition duration-500 group-hover:scale-[1.02]">
                        @if ($loop->last && $images->count() > 5)
                            <span class="absolute inset-0 grid place-items-center bg-ink/55 font-semibold text-white">+{{ $images->count() - 5 }} photos</span>
                        @endif
                    </button>
                @endforeach
            </div>
            @if ($images->count() > 1)
                <button type="button" @click="show(0)" class="btn-line btn-sm mt-3 sm:hidden"><x-icon name="image" class="size-3.5" /> Voir les {{ $images->count() }} photos</button>
            @endif

            {{-- Visionneuse --}}
            <div x-show="open" x-cloak x-transition.opacity @keydown.escape.window="open = false" @keydown.arrow-right.window="open && next()" @keydown.arrow-left.window="open && prev()"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-ink/95 p-4" role="dialog" aria-modal="true" aria-label="Photos du bien">
                <button type="button" @click="open = false" class="absolute top-4 right-4 rounded-full p-2 text-white/80 hover:bg-white/10 hover:text-white" aria-label="Fermer"><x-icon name="x" class="size-6" /></button>
                <button type="button" @click="prev()" class="absolute left-2 rounded-full p-3 text-white/80 hover:bg-white/10 sm:left-6" aria-label="Photo précédente"><x-icon name="arrow-left" class="size-6" /></button>
                <img :src="images[current]" alt="" class="max-h-[85vh] max-w-full rounded-lg object-contain">
                <button type="button" @click="next()" class="absolute right-2 rounded-full p-3 text-white/80 hover:bg-white/10 sm:right-6" aria-label="Photo suivante"><x-icon name="arrow-right" class="size-6" /></button>
                <p class="absolute bottom-4 text-sm text-white/70"><span x-text="current + 1"></span> / <span x-text="images.length"></span></p>
            </div>
        </div>

        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_360px]">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span @class(['rounded px-2 py-1 text-[11px] font-bold tracking-wide uppercase', 'bg-ink text-white' => $bien->est_location, 'bg-roof text-ink' => ! $bien->est_location])>{{ $bien->est_location ? 'À louer' : 'À vendre' }}</span>
                    @if ($bien->categorie)<span class="tag">{{ $bien->categorie->DESIGNATION }}</span>@endif
                    @if (strtoupper((string) $bien->LUXE) === 'OUI')<span class="tag border-ink bg-ink text-roof">Prestige</span>@endif
                    @if ($bien->MATRICULE)<span class="text-xs text-muted">Réf. {{ $bien->MATRICULE }}</span>@endif
                </div>
                <h1 class="mt-3 text-3xl font-semibold leading-tight sm:text-4xl">{{ $titreLisible }}</h1>
                <p class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-1 text-ink/70">
                    <span class="inline-flex items-center gap-1.5"><x-icon name="map-pin" class="size-4" /> {{ $adresse }}</span>
                    <x-vues :nombre="$fiche->vues_affichees" class="text-sm" />
                </p>
                <p class="mt-5 font-serif text-3xl font-semibold text-ink">{{ $prix }}</p>

                @if ($carac)
                    <dl class="mt-8 flex flex-wrap overflow-hidden rounded-xl border-t border-l border-line bg-white">
                        @foreach ($carac as [$icone, $label, $valeur])
                            <div class="min-w-[50%] flex-1 border-r border-b border-line p-4 sm:min-w-[25%]">
                                <dt class="flex items-center gap-1.5 text-xs text-muted"><x-icon :name="$icone" class="size-3.5" /> {{ $label }}</dt>
                                <dd class="mt-1 font-semibold">{{ $valeur }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($bien->DESCRIPTION)
                    <section class="mt-10">
                        <h2 class="text-xl font-semibold">Description</h2>
                        <div class="prose-pratik mt-3 whitespace-pre-line">{{ trim(strip_tags(str_ireplace(['<br>', '<br/>', '<br />', '</p>'], "\n", $bien->DESCRIPTION))) }}</div>
                    </section>
                @endif

                @if ($bien->equipements_liste)
                    <section class="mt-10">
                        <h2 class="text-xl font-semibold">Équipements</h2>
                        <ul class="mt-4 grid gap-x-6 gap-y-2.5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($bien->equipements_liste as $eq)
                                <li class="flex items-center gap-2 text-ink/85"><x-icon name="check" class="size-4 text-garde" /> {{ $eq }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($video || $visite3d)
                    <section class="mt-10">
                        <h2 class="text-xl font-semibold">Vidéo et visite</h2>
                        @if ($video)
                            <div class="mt-4 aspect-video overflow-hidden rounded-xl bg-ink">
                                <iframe src="{{ $video }}" title="Vidéo du bien" loading="lazy" class="size-full" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        @endif
                        @if ($visite3d)
                            @if ($visite3d['integrable'])
                                <div class="mt-4 aspect-video overflow-hidden rounded-xl bg-sand">
                                    <iframe src="{{ $visite3d['url'] }}" title="Visite virtuelle" loading="lazy" class="size-full" allowfullscreen></iframe>
                                </div>
                            @else
                                <a href="{{ $visite3d['url'] }}" target="_blank" rel="noopener nofollow" class="btn-line mt-4">Visite virtuelle 3D <x-icon name="external" class="size-4" /></a>
                            @endif
                        @endif
                    </section>
                @endif

                @if ($bien->a_geoloc || $googleMaps)
                    <section class="mt-10">
                        <h2 class="text-xl font-semibold">Emplacement</h2>
                        @if ($bien->a_geoloc)
                            <div class="mt-4 h-80 overflow-hidden rounded-xl border border-line"
                                 x-data="pratikMap({ markers: @js([['lat' => (float) $bien->LATITUDE, 'lng' => (float) $bien->LONGITUDE, 'title' => $fiche->TITRE, 'subtitle' => $commune]]), zoom: 15 })"></div>
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ (float) $bien->LATITUDE }},{{ (float) $bien->LONGITUDE }}" target="_blank" rel="noopener" class="link mt-3 inline-flex items-center gap-1 text-sm"><x-icon name="navigation" class="size-3.5" /> Itinéraire vers le bien</a>
                        @elseif ($googleMaps['embed'])
                            <div class="mt-4 h-80 overflow-hidden rounded-xl border border-line">
                                <iframe src="{{ $googleMaps['embed'] }}" title="Carte" loading="lazy" class="size-full" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        @else
                            <a href="{{ $googleMaps['lien'] }}" target="_blank" rel="noopener nofollow" class="btn-line mt-4"><x-icon name="map-pin" class="size-4" /> Voir sur Google Maps</a>
                        @endif
                    </section>
                @endif
            </div>

            {{-- Annonceur + contact --}}
            <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start" id="contact">
                <div class="card p-5">
                    <p class="text-xs font-semibold tracking-wide text-muted uppercase">Proposé par</p>
                    <div class="mt-3 flex items-center gap-3">
                        <x-logo-tile :src="$logo" :name="$contacts['nom']" />
                        <div class="min-w-0">
                            <p class="font-semibold leading-snug">{{ $contacts['nom'] }}</p>
                            @if ($societe?->LIEN)
                                <a href="{{ route('immo.annonceur', $societe->LIEN) }}" class="text-sm text-roof-deep hover:underline">Voir toutes ses annonces</a>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 grid gap-2">
                        @foreach ($contacts['telephones'] as $tel)
                            <a href="{{ tel_link($tel) }}" onclick="{{ $suivi('appel') }}" class="{{ $loop->first ? 'btn-ink' : 'btn-line' }} justify-start"><x-icon name="phone" class="size-4" /> {{ $tel }}</a>
                        @endforeach
                        @if ($contacts['whatsapp'])
                            <a href="https://wa.me/{{ $contacts['whatsapp'] }}?text={{ rawurlencode('Bonjour, je suis intéressé(e) par votre annonce « '.$fiche->TITRE.' » vue sur Pratik : '.route('immo.bien', $fiche->LIEN)) }}"
                               onclick="{{ $suivi('appel') }}" target="_blank" rel="noopener" class="btn justify-start bg-[#1f8f4e] text-white hover:brightness-110"><x-icon name="whatsapp" class="size-4" /> Écrire sur WhatsApp</a>
                        @endif
                        @if ($contacts['email'])
                            <a href="mailto:{{ $contacts['email'] }}?subject={{ rawurlencode('Annonce Pratik : '.$fiche->TITRE) }}" onclick="{{ $suivi('email') }}" class="btn-line justify-start"><x-icon name="mail" class="size-4" /> {{ $contacts['email'] }}</a>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('immo.bien.contact', $fiche->LIEN) }}" class="card space-y-3 p-5">
                    @csrf
                    <p class="font-semibold">Envoyer un message</p>
                    <div class="hidden" aria-hidden="true"><label>Site web <input name="site_web" tabindex="-1" autocomplete="off"></label></div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="sr-only" for="c-prenom">Prénom</label>
                            <input id="c-prenom" name="prenom" value="{{ old('prenom', auth()->user()?->name) }}" placeholder="Prénom" required class="input">
                        </div>
                        <div>
                            <label class="sr-only" for="c-nom">Nom</label>
                            <input id="c-nom" name="nom" value="{{ old('nom') }}" placeholder="Nom" required class="input">
                        </div>
                    </div>
                    @error('prenom')<p class="input-error">{{ $message }}</p>@enderror
                    @error('nom')<p class="input-error">{{ $message }}</p>@enderror
                    <div>
                        <label class="sr-only" for="c-tel">Téléphone</label>
                        <input id="c-tel" name="telephone" value="{{ old('telephone') }}" placeholder="Téléphone" inputmode="tel" required class="input">
                        @error('telephone')<p class="input-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="sr-only" for="c-email">E-mail</label>
                        <input id="c-email" type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" placeholder="E-mail" required class="input">
                        @error('email')<p class="input-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="sr-only" for="c-message">Message</label>
                        <textarea id="c-message" name="message" rows="4" required class="input">{{ old('message', 'Bonjour, je suis intéressé(e) par ce bien. Est-il toujours disponible ? Je souhaiterais organiser une visite.') }}</textarea>
                        @error('message')<p class="input-error">{{ $message }}</p>@enderror
                    </div>
                    <button class="btn-roof w-full">Envoyer à l'annonceur</button>
                    <p class="text-xs text-muted">Vos coordonnées sont transmises uniquement à l'annonceur de ce bien.</p>
                </form>
            </aside>
        </div>

        @if ($similaires->isNotEmpty())
            <section class="mt-16 border-t border-line pt-10">
                <h2 class="section-title">Biens similaires</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similaires as $s)
                        <x-bien-card :bien="$s" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'RealEstateListing',
        'name' => $fiche->TITRE,
        'url' => route('immo.bien', $fiche->LIEN),
        'description' => $resume ?: null,
        'image' => $images->all(),
        'datePosted' => $bien->DATE_LOG ?: null,
        'offers' => array_filter([
            '@type' => 'Offer',
            'price' => (int) preg_replace('/\D/', '', (string) $bien->PRIX) ?: null,
            'priceCurrency' => 'XOF',
            'businessFunction' => $bien->est_location ? 'http://purl.org/goodrelations/v1#LeaseOut' : 'http://purl.org/goodrelations/v1#Sell',
        ]),
        'contentLocation' => $commune ? ['@type' => 'Place', 'name' => $commune.', Côte d\'Ivoire'] : null,
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
