@extends('layouts.site')

@php
    $commune = $entreprise->commune;
    $titrePage = $entreprise->TITRE.($commune ? ' à '.$commune->DESIGNATION : '');
    $resume = \App\Services\HtmlPropre::texte($annuaire->RESUME ?: $annuaire->DESCRIPTION ?: $entreprise->ACTIVITE, 158)
        ?: $entreprise->TITRE.' : coordonnées, adresse et plan d\'accès sur Pratik.';
    $telephones = $annuaire->telephones;
    $emails = $annuaire->emails;
    $whatsapp = preg_replace('/\D+/', '', (string) $annuaire->WHATSAPP);
    if ($whatsapp !== '' && strlen($whatsapp) === 10) {
        $whatsapp = '225'.$whatsapp;
    }
    $adresse = trim((string) ($annuaire->ADRESSE ?: $annuaire->LOCALISATION));
    $itineraire = $annuaire->a_geoloc
        ? 'https://www.google.com/maps/dir/?api=1&destination='.$annuaire->LATITUDE.','.$annuaire->LONGITUDE
        : (str_starts_with((string) $annuaire->GEOLOCALISATION, 'http') ? $annuaire->GEOLOCALISATION : null);
    $reseaux = array_filter([
        'facebook' => $annuaire->FACEBOOK,
        'instagram' => $annuaire->INSTAGRAM,
        'linkedin' => $annuaire->LINKEDIN,
        'x-social' => $annuaire->TWEETER,
    ], fn ($u) => is_string($u) && preg_match('#^https?://#i', trim($u)));
    $photos = $annuaire->photos->map(fn ($p) => $p->url)->filter()->values();
    $description = \App\Services\HtmlPropre::nettoyer($annuaire->DESCRIPTION);
    $fil = ['Accueil' => route('home'), 'Annuaire' => route('annuaire.index')];
    if ($categoriePrincipale) {
        $fil[$categoriePrincipale->categorie->DESIGNATION] = route('annuaire.categorie', $categoriePrincipale->categorie->LIEN);
        $fil[$categoriePrincipale->DESIGNATION] = route('annuaire.sous-categorie', [$categoriePrincipale->categorie->LIEN, $categoriePrincipale->LIEN]);
    }
    $fil[$entreprise->TITRE] = null;
@endphp

@section('title', $titrePage)
@section('description', $resume)
@section('canonical', route('annuaire.fiche', $entreprise->LIEN))
@if ($annuaire->logo_url)
    @section('og_image', $annuaire->logo_url)
@endif

@section('content')
    <section class="border-b border-line bg-white">
        <div class="container-page py-8">
            <nav class="mb-5 flex flex-wrap items-center gap-1 text-[13px] text-muted" aria-label="Fil d'Ariane">
                @foreach ($fil as $label => $url)
                    @if (! $loop->first)<x-icon name="chevron-right" class="size-3.5" />@endif
                    @if ($url)<a href="{{ $url }}" class="hover:text-ink">{{ $label }}</a>@else<span class="line-clamp-1 text-ink/80">{{ $label }}</span>@endif
                @endforeach
            </nav>

            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <x-logo-tile :src="$annuaire->logo_url" :name="$entreprise->TITRE" size="size-24" class="rounded-xl" />
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($annuaire->est_client)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber px-3 py-1 text-xs font-bold tracking-wide text-ink" title="Entreprise partenaire de Pratik"><x-icon name="crown" class="size-4" /> TOP 12</span>
                        @endif
                        @if ($annuaire->nature?->DESIGNATION)
                            <span class="tag">{{ $annuaire->nature->DESIGNATION }}</span>
                        @endif
                        @if ($annuaire->FORME_JURIDIQUE)
                            <span class="tag">{{ $annuaire->FORME_JURIDIQUE }}</span>
                        @endif
                    </div>
                    <h1 class="mt-2 text-3xl font-semibold leading-tight sm:text-4xl">{{ $entreprise->TITRE }}</h1>
                    @if ($entreprise->SIGLE && mb_strtolower($entreprise->SIGLE) !== mb_strtolower($entreprise->TITRE))
                        <p class="mt-1 text-lg text-ink/60">{{ $entreprise->SIGLE }}</p>
                    @endif
                    <p class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-ink/75">
                        @if ($commune)<span class="inline-flex items-center gap-1.5"><x-icon name="map-pin" class="size-4" /> {{ $commune->DESIGNATION }}</span>@endif
                        <x-vues :nombre="$entreprise->vues_affichees" />
                        @if ($entreprise->ACTIVITE)<span class="line-clamp-1">{{ $entreprise->ACTIVITE }}</span>@endif
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @if ($telephones)
                            <a href="{{ tel_link($telephones[0]) }}" onclick="pratikTrack('{{ route('stats.appel', $entreprise->ID) }}')" class="btn-ink"><x-icon name="phone" class="size-4" /> Appeler</a>
                        @endif
                        @if ($whatsapp)
                            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" onclick="pratikTrack('{{ route('stats.appel', $entreprise->ID) }}')" class="btn-line"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
                        @endif
                        @if ($emails)
                            <a href="#contacter" class="btn-line"><x-icon name="mail" class="size-4" /> Écrire</a>
                        @endif
                        @if ($itineraire)
                            <a href="{{ $itineraire }}" target="_blank" rel="noopener" class="btn-line"><x-icon name="navigation" class="size-4" /> Itinéraire</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container-page grid gap-8 py-10 lg:grid-cols-[1fr_360px]">
        <div class="min-w-0 space-y-10">
            @if ($description || $annuaire->ACTIVITES || $annuaire->SPECIALITE)
                <section>
                    <h2 class="section-title">Présentation</h2>
                    @if ($description)
                        <div class="prose-pratik mt-4 max-w-none">{!! $description !!}</div>
                    @endif
                    @if ($annuaire->ACTIVITES || $annuaire->SPECIALITE)
                        <dl @class(['mt-5 grid gap-4', 'sm:grid-cols-2' => $annuaire->ACTIVITES && $annuaire->SPECIALITE])>
                            @if ($annuaire->ACTIVITES)
                                <div class="rounded-lg bg-sand/60 p-4"><dt class="text-xs font-semibold tracking-wide text-muted uppercase">Activités</dt><dd class="mt-1 text-sm">{{ $annuaire->ACTIVITES }}</dd></div>
                            @endif
                            @if ($annuaire->SPECIALITE)
                                <div class="rounded-lg bg-sand/60 p-4"><dt class="text-xs font-semibold tracking-wide text-muted uppercase">Spécialités</dt><dd class="mt-1 text-sm">{{ $annuaire->SPECIALITE }}</dd></div>
                            @endif
                        </dl>
                    @endif
                </section>
            @endif

            @if ($photos->isNotEmpty())
                <section x-data="gallery(@js($photos))">
                    <h2 class="section-title">Photos</h2>
                    <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach ($photos as $i => $photo)
                            <button type="button" @click="show({{ $i }})" class="aspect-[4/3] overflow-hidden rounded-lg bg-sand">
                                <img src="{{ $photo }}" alt="{{ $entreprise->TITRE }}, photo {{ $i + 1 }}" loading="lazy" class="size-full object-cover transition hover:scale-[1.03]">
                            </button>
                        @endforeach
                    </div>
                    <div x-show="open" x-cloak x-transition.opacity @keydown.escape.window="open = false" @keydown.arrow-right.window="next()" @keydown.arrow-left.window="prev()"
                         class="fixed inset-0 z-50 flex items-center justify-center bg-ink/90 p-4" @click.self="open = false">
                        <button type="button" @click="open = false" class="absolute top-4 right-4 rounded-full p-2 text-white/80 hover:text-white" aria-label="Fermer"><x-icon name="x" class="size-6" /></button>
                        <button type="button" @click="prev()" class="absolute left-2 rounded-full p-3 text-white/80 hover:text-white sm:left-6" aria-label="Précédente"><x-icon name="arrow-left" class="size-6" /></button>
                        <img :src="images[current]" alt="" class="max-h-[85vh] max-w-full rounded-lg object-contain">
                        <button type="button" @click="next()" class="absolute right-2 rounded-full p-3 text-white/80 hover:text-white sm:right-6" aria-label="Suivante"><x-icon name="arrow-right" class="size-6" /></button>
                    </div>
                </section>
            @endif

            @if ($annuaire->a_geoloc)
                <section>
                    <h2 class="section-title">Plan d'accès</h2>
                    @if ($adresse)<p class="mt-2 text-ink/75">{{ $adresse }}</p>@endif
                    <div class="mt-4 h-80 overflow-hidden rounded-xl border border-line"
                         x-data="pratikMap({ markers: @js([['lat' => (float) $annuaire->LATITUDE, 'lng' => (float) $annuaire->LONGITUDE, 'title' => $entreprise->TITRE, 'subtitle' => $adresse]]) })"></div>
                </section>
            @endif

            @if ($departements->isNotEmpty())
                <section>
                    <h2 class="section-title">Services et départements</h2>
                    <ul class="mt-4 divide-y divide-line rounded-xl border border-line bg-white">
                        @foreach ($departements as $d)
                            <li class="flex flex-col gap-1 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <span class="font-medium">{{ $d->LIBELLE }}</span>
                                <span class="flex flex-wrap gap-x-4 text-sm text-ink/75">
                                    @foreach (array_filter([$d->TEL, $d->CEL]) as $t)<a href="{{ tel_link($t) }}" class="hover:text-ink">{{ $t }}</a>@endforeach
                                    @if ($d->EMAIL)<a href="mailto:{{ $d->EMAIL }}" class="hover:text-ink">{{ $d->EMAIL }}</a>@endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($succursales->isNotEmpty())
                <section>
                    <h2 class="section-title">Agences et succursales</h2>
                    <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach ($succursales as $s)
                            <li>
                                <a href="{{ route('annuaire.fiche', $s->entreprise->LIEN) }}" class="card card-hover block p-4">
                                    <span class="font-medium">{{ $s->entreprise->TITRE }}</span>
                                    <span class="mt-1 block text-sm text-muted">{{ $s->entreprise->commune?->DESIGNATION }}{{ $s->ADRESSE ? ' · '.\Illuminate\Support\Str::limit($s->ADRESSE, 60) : '' }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($biens->isNotEmpty())
                <section>
                    <div class="flex items-end justify-between gap-4">
                        <h2 class="section-title">Ses annonces immobilières</h2>
                        <a href="{{ route('immo.home') }}" target="_blank" rel="noopener" class="link text-sm">Pratik Immobilier</a>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($biens as $bien)
                            <x-bien-card :bien="$bien" />
                        @endforeach
                    </div>
                </section>
            @endif

            <section id="contacter" class="scroll-mt-28">
                <h2 class="section-title">Contacter {{ $entreprise->SIGLE ?: $entreprise->TITRE }}</h2>
                <p class="mt-1 text-sm text-muted">Votre message est transmis directement à l'entreprise.</p>
                <form method="POST" action="{{ route('annuaire.contact', $entreprise->ID) }}" class="card relative mt-4 grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    @csrf
                    <x-site.anti-spam />
                    <div><label class="label" for="c-nom">Nom</label><input id="c-nom" name="nom" value="{{ old('nom') }}" required class="input">@error('nom')<p class="input-error">{{ $message }}</p>@enderror</div>
                    <div><label class="label" for="c-prenom">Prénom</label><input id="c-prenom" name="prenom" value="{{ old('prenom') }}" class="input"></div>
                    <div><label class="label" for="c-tel">Téléphone</label><input id="c-tel" name="contact" value="{{ old('contact') }}" required class="input">@error('contact')<p class="input-error">{{ $message }}</p>@enderror</div>
                    <div><label class="label" for="c-email">E-mail</label><input id="c-email" type="email" name="email" value="{{ old('email') }}" required class="input">@error('email')<p class="input-error">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><label class="label" for="c-objet">Objet</label><input id="c-objet" name="objet" value="{{ old('objet') }}" required class="input">@error('objet')<p class="input-error">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><label class="label" for="c-message">Message</label><textarea id="c-message" name="message" rows="5" required class="input">{{ old('message') }}</textarea>@error('message')<p class="input-error">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><button class="btn-ink">Envoyer le message</button></div>
                </form>
            </section>
        </div>

        {{-- Coordonnées --}}
        <aside class="lg:sticky lg:top-28 lg:self-start">
            <div class="card divide-y divide-line">
                <div class="p-5">
                    <h2 class="font-sans text-sm font-semibold tracking-wide text-muted uppercase">Coordonnées</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($telephones as $tel)
                            <li><a href="{{ tel_link($tel) }}" onclick="pratikTrack('{{ route('stats.appel', $entreprise->ID) }}')" class="flex items-center gap-3 hover:text-amber-deep"><x-icon name="phone" class="size-4 text-muted" /> {{ $tel }}</a></li>
                        @endforeach
                        @if ($annuaire->FAX)
                            <li class="flex items-center gap-3"><x-icon name="file-text" class="size-4 text-muted" /> Fax : {{ $annuaire->FAX }}</li>
                        @endif
                        @foreach ($emails as $email)
                            <li><a href="mailto:{{ $email }}" onclick="pratikTrack('{{ route('stats.email', $entreprise->ID) }}')" class="flex items-center gap-3 break-all hover:text-amber-deep"><x-icon name="mail" class="size-4 shrink-0 text-muted" /> {{ $email }}</a></li>
                        @endforeach
                        @if ($annuaire->site_url)
                            <li><a href="{{ $annuaire->site_url }}" target="_blank" rel="noopener nofollow" class="flex items-center gap-3 break-all hover:text-amber-deep"><x-icon name="globe" class="size-4 shrink-0 text-muted" /> {{ preg_replace('#^https?://(www\.)?#', '', rtrim($annuaire->site_url, '/')) }}</a></li>
                        @endif
                        @if ($adresse)
                            <li class="flex gap-3"><x-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-muted" /> <span>{{ $adresse }}@if ($commune && ! str_contains(mb_strtolower($adresse), mb_strtolower($commune->DESIGNATION))), {{ $commune->DESIGNATION }}@endif</span></li>
                        @endif
                        @if ($annuaire->BP)
                            <li class="flex items-center gap-3"><x-icon name="inbox" class="size-4 text-muted" /> {{ $annuaire->BP }}</li>
                        @endif
                        @if (! $telephones && ! $emails && ! $adresse)
                            <li class="text-muted">Coordonnées non renseignées pour le moment.</li>
                        @endif
                    </ul>
                    @if ($reseaux)
                        <div class="mt-5 flex gap-2">
                            @foreach ($reseaux as $icone => $url)
                                <a href="{{ trim($url) }}" target="_blank" rel="noopener nofollow" class="grid size-9 place-items-center rounded-full border border-line hover:border-ink/30" aria-label="{{ $icone }}"><x-icon :name="$icone" class="size-4" /></a>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($siege)
                    <div class="p-5 text-sm">
                        <p class="text-muted">Rattachée au siège</p>
                        <a href="{{ route('annuaire.fiche', $siege->LIEN) }}" class="link mt-1 inline-block font-medium">{{ $siege->TITRE }}</a>
                    </div>
                @endif

                @if ($thematiques->isNotEmpty())
                    <div class="p-5">
                        <p class="text-sm text-muted">Rubriques</p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($thematiques as $t)
                                <a href="{{ route('annuaire.sous-categorie', [$t->sousCategorie->categorie->LIEN, $t->sousCategorie->LIEN]) }}" class="tag hover:border-ink/30">{{ $t->sousCategorie->DESIGNATION }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-paper p-5 text-sm">
                    <p class="text-ink/75">C'est votre entreprise ?</p>
                    <a href="{{ route('souscription') }}" class="link mt-1 inline-block font-medium">Mettre à jour cette fiche</a>
                </div>
            </div>
        </aside>
    </div>

    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $entreprise->TITRE,
        'alternateName' => $entreprise->SIGLE ?: null,
        'url' => route('annuaire.fiche', $entreprise->LIEN),
        'image' => $annuaire->logo_url,
        'description' => $resume,
        'telephone' => $telephones[0] ?? null,
        'email' => $emails[0] ?? null,
        'sameAs' => array_values(array_filter(array_merge([$annuaire->site_url], array_values($reseaux)))) ?: null,
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $adresse ?: null,
            'addressLocality' => $commune?->DESIGNATION,
            'addressCountry' => 'CI',
        ]),
        'geo' => $annuaire->a_geoloc ? ['@type' => 'GeoCoordinates', 'latitude' => (float) $annuaire->LATITUDE, 'longitude' => (float) $annuaire->LONGITUDE] : null,
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
