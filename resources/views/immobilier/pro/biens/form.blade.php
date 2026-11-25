@extends('layouts.immo')

@php
    $edition = $bien->exists;
    $v = fn (string $champ, string $colonne) => old($champ, $bien->{$colonne});
    $equipementsCoches = old('equipements', collect(\App\Models\Immobilier::EQUIPEMENTS)->keys()->filter(fn ($c) => strtoupper((string) $bien->{$c}) === 'OUI')->values()->all());
    $libellesTypes = [
        1 => ['Location', 'Agence : bien à louer'],
        2 => ['Vente', 'Agence : bien à vendre'],
        3 => ['Programme neuf', 'Promoteur : vente'],
        4 => ['Terrain / lotissement', 'Aménageur foncier'],
    ];
@endphp

@section('title', $edition ? 'Modifier mon annonce' : 'Déposer une annonce')
@section('noindex', true)

@section('content')
    <x-immo.pro-nav />

    <div class="container-page py-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">{{ $edition ? $bien->entreprise?->TITRE : 'Nouvelle annonce' }}</h1>
                @if ($edition)
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                        <x-immo.statut :validation="$bien->VALIDATION" />
                        @if ($bien->entreprise?->LIEN)
                            <a href="{{ route('immo.bien', $bien->entreprise->LIEN) }}" target="_blank" class="link">Voir l'annonce</a>
                        @endif
                    </div>
                @else
                    <p class="mt-1 text-sm text-muted">Votre annonce sera vérifiée par l'équipe Pratik avant sa mise en ligne, en général sous 24 h.</p>
                @endif
            </div>
        </div>

        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-danger/30 bg-red-50 px-4 py-3 text-sm text-danger">
                <p class="font-semibold">Merci de corriger les points suivants :</p>
                <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ $edition ? route('immo.pro.biens.update', $bien->ID) : route('immo.pro.biens.store') }}"
              class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]" x-data="{ type: {{ (int) old('type_immobilier_id', $bien->TYPE_IMMOBILIER_ID ?: 2) }}, envoi: false }" @submit="envoi = true">
            @csrf
            @if ($edition) @method('PUT') @endif

            <div class="space-y-6">
                {{-- L'essentiel --}}
                <section class="card space-y-5 p-6">
                    <h2 class="font-sans text-base font-semibold">L'essentiel</h2>
                    <div>
                        <label class="label" for="titre">Titre de l'annonce</label>
                        <input id="titre" name="titre" value="{{ old('titre', $bien->entreprise?->TITRE) }}" required maxlength="250" class="input" placeholder="Ex. Villa duplex 5 pièces avec piscine à Cocody Riviera 3">
                        <p class="mt-1 text-xs text-muted">Indiquez le type de bien, sa taille et le quartier : c'est ce que les gens cherchent.</p>
                    </div>
                    <fieldset>
                        <legend class="label">Type d'offre</legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($libellesTypes as $id => [$titre, $sous])
                                <label class="cursor-pointer">
                                    <input type="radio" name="type_immobilier_id" value="{{ $id }}" x-model.number="type" class="peer sr-only" @checked((int) old('type_immobilier_id', $bien->TYPE_IMMOBILIER_ID ?: 2) === $id)>
                                    <span class="block rounded-lg border border-line bg-white px-4 py-3 peer-checked:border-ink peer-checked:ring-1 peer-checked:ring-ink peer-focus-visible:ring-2 peer-focus-visible:ring-roof">
                                        <span class="block text-sm font-semibold">{{ $titre }}</span>
                                        <span class="block text-xs text-muted">{{ $sous }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="categorie">Catégorie</label>
                            <select id="categorie" name="categorie_immobilier_id" required class="input">
                                <option value="">Choisir…</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->ID }}" @selected($v('categorie_immobilier_id', 'CATEGORIE_IMMOBILIER_ID') == $c->ID)>{{ $c->DESIGNATION }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label" for="commune">Commune</label>
                            <select id="commune" name="commune_id" required class="input">
                                <option value="">Choisir…</option>
                                @foreach ($communes as $c)
                                    <option value="{{ $c->ID }}" @selected(old('commune_id', $bien->entreprise?->COMMUNE_ID) == $c->ID)>{{ $c->DESIGNATION }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label class="label" for="prix"><span x-text="type === 1 ? 'Loyer (FCFA)' : 'Prix de vente (FCFA)'">Prix (FCFA)</span></label>
                            <input id="prix" name="prix" inputmode="numeric" value="{{ old('prix', $bien->PRIX ? number_format((int) preg_replace('/\D/', '', $bien->PRIX), 0, ',', ' ') : '') }}" class="input" placeholder="Laisser vide pour « prix sur demande »">
                        </div>
                        <div x-show="type === 1">
                            <label class="label" for="periode">Par</label>
                            <select id="periode" name="periode" class="input">
                                <option value="MOIS" @selected(old('periode', $bien->PERIODE) !== 'JOUR')>mois</option>
                                <option value="JOUR" @selected(old('periode', $bien->PERIODE) === 'JOUR')>jour (meublé)</option>
                            </select>
                        </div>
                        <div x-show="type !== 1">
                            <label class="label" for="prix_m2">Prix au m²</label>
                            <input id="prix_m2" name="prix_m2" inputmode="numeric" value="{{ old('prix_m2', $bien->PRIX_M2) }}" class="input">
                        </div>
                    </div>
                </section>

                {{-- Caractéristiques --}}
                <section class="card p-6">
                    <h2 class="font-sans text-base font-semibold">Caractéristiques</h2>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @foreach (['surface' => ['SURFACE', 'Surface (m²)'], 'pieces' => ['PIECES', 'Pièces'], 'chambre' => ['CHAMBRE', 'Chambres'], 'salle_bain' => ['SALLE_BAIN', 'Salles de bain'], 'salons' => ['SALONS', 'Salons'], 'age' => ['AGE', 'Âge (années)']] as $champ => [$col, $label])
                            <div>
                                <label class="label" for="{{ $champ }}">{{ $label }}</label>
                                <input id="{{ $champ }}" name="{{ $champ }}" type="number" min="0" step="{{ $champ === 'surface' ? 'any' : '1' }}" value="{{ $v($champ, $col) }}" class="input">
                            </div>
                        @endforeach
                        <div class="col-span-2">
                            <label class="label" for="etat_bien">État</label>
                            <select id="etat_bien" name="etat_bien" class="input">
                                <option value="">Non précisé</option>
                                <option value="NEUF" @selected($v('etat_bien', 'ETAT_BIEN') === 'NEUF')>Neuf</option>
                                <option value="OCCASION" @selected($v('etat_bien', 'ETAT_BIEN') === 'OCCASION')>Ancien / occasion</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="mt-7 font-sans text-sm font-semibold">Équipements</h3>
                    <div class="mt-3 grid gap-x-4 gap-y-2.5 sm:grid-cols-3">
                        @foreach (\App\Models\Immobilier::EQUIPEMENTS as $col => $label)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="equipements[]" value="{{ $col }}" class="rounded border-line text-ink focus:ring-roof" @checked(in_array($col, $equipementsCoches, true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- Description --}}
                <section class="card space-y-4 p-6">
                    <h2 class="font-sans text-base font-semibold">Description</h2>
                    <div>
                        <label class="sr-only" for="description">Description</label>
                        <textarea id="description" name="description" rows="8" class="input" placeholder="Décrivez le bien : agencement, finitions, environnement, accès, conditions (caution, avance…).">{{ old('description', trim(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], "\n", (string) $bien->DESCRIPTION)))) }}</textarea>
                    </div>
                    <div>
                        <label class="label" for="mots_cles">Mots-clés <span class="font-normal text-muted">(facultatif, séparés par des virgules)</span></label>
                        <input id="mots_cles" name="mots_cles" value="{{ $v('mots_cles', 'MOTS_CLES') }}" class="input" placeholder="villa, piscine, Riviera, meublé">
                    </div>
                </section>

                {{-- Photos --}}
                <section class="card space-y-5 p-6">
                    <h2 class="font-sans text-base font-semibold">Photos</h2>
                    <div class="grid gap-5 sm:grid-cols-[180px_1fr]" x-data="photosPreview()">
                        <div class="aspect-[4/3] overflow-hidden rounded-lg border border-line bg-sand">
                            <template x-if="apercus.length"><img :src="apercus[0].url" alt="" class="size-full object-cover"></template>
                            <template x-if="! apercus.length">
                                @if ($edition)
                                    <img src="{{ $bien->image_url }}" alt="Photo principale actuelle" class="size-full object-cover">
                                @else
                                    <div class="grid size-full place-items-center text-ink/30"><x-icon name="image" class="size-8" /></div>
                                @endif
                            </template>
                        </div>
                        <div>
                            <label class="label" for="illustration">Photo principale</label>
                            <input id="illustration" type="file" name="illustration" accept="image/jpeg,image/png,image/webp" @change="choisir($event)" class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-ink file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                            <p class="mt-1 text-xs text-muted">La photo affichée dans les résultats. JPG, PNG ou WebP, 8 Mo maximum.@if ($edition) La nouvelle photo remplace l'ancienne.@endif</p>
                        </div>
                    </div>

                    <div x-data="photosPreview()">
                        <label class="label" for="photos">Autres photos <span class="font-normal text-muted">(jusqu'à 15 à la fois)</span></label>
                        <input id="photos" type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" @change="choisir($event)" class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-sand file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink">
                        <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-6" x-show="apercus.length" x-cloak>
                            <template x-for="a in apercus" :key="a.url">
                                <img :src="a.url" :alt="a.nom" class="aspect-square rounded-md object-cover ring-2 ring-roof">
                            </template>
                        </div>
                    </div>

                    @if ($edition && $bien->photos->isNotEmpty())
                        <div>
                            <p class="text-sm font-medium">Galerie actuelle ({{ $bien->photos->count() }})</p>
                            <div class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-6">
                                @foreach ($bien->photos as $photo)
                                    <div class="group relative">
                                        <img src="{{ $photo->url }}" alt="" loading="lazy" class="aspect-square w-full rounded-md bg-sand object-cover">
                                        <button type="submit" form="supprimer-photo-{{ $photo->ID }}" class="absolute top-1 right-1 rounded-full bg-white/95 p-1.5 text-danger shadow hover:bg-white" title="Supprimer cette photo"
                                                onclick="return confirm('Supprimer cette photo ?')"><x-icon name="trash" class="size-3.5" /></button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Emplacement --}}
                <section class="card space-y-4 p-6" x-data="mapPicker({ lat: @js($v('latitude', 'LATITUDE') ?: null), lng: @js($v('longitude', 'LONGITUDE') ?: null) })">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="font-sans text-base font-semibold">Emplacement</h2>
                        <div class="flex gap-2">
                            <button type="button" @click="maPosition()" class="btn-line btn-sm"><x-icon name="locate" class="size-3.5" /> <span x-text="locating ? 'Localisation…' : 'Je suis sur place'">Je suis sur place</span></button>
                            <button type="button" @click="effacer()" class="btn-ghost btn-sm">Effacer</button>
                        </div>
                    </div>
                    <div>
                        <label class="label" for="adresse">Adresse / quartier</label>
                        <input id="adresse" name="adresse" value="{{ $v('adresse', 'ADRESSE') }}" class="input" placeholder="Ex. Riviera 3, non loin du carrefour Faya">
                    </div>
                    <p class="text-sm text-muted">Cliquez sur la carte pour placer le bien, puis déplacez le repère si besoin. Les annonces localisées apparaissent sur la carte du site.</p>
                    <div x-ref="carte" class="h-72 overflow-hidden rounded-lg border border-line"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="label" for="latitude">Latitude</label><input id="latitude" x-ref="lat" name="latitude" value="{{ $v('latitude', 'LATITUDE') }}" class="input" inputmode="decimal"></div>
                        <div><label class="label" for="longitude">Longitude</label><input id="longitude" x-ref="lng" name="longitude" value="{{ $v('longitude', 'LONGITUDE') }}" class="input" inputmode="decimal"></div>
                    </div>
                    <div>
                        <label class="label" for="google_maps">Lien Google Maps <span class="font-normal text-muted">(facultatif)</span></label>
                        <input id="google_maps" name="google_maps" type="url" value="{{ preg_match('~^https?://~', (string) $v('google_maps', 'GOOGLE_MAPS')) ? $v('google_maps', 'GOOGLE_MAPS') : '' }}" class="input" placeholder="https://maps.app.goo.gl/…">
                    </div>
                </section>

                {{-- Vidéo --}}
                <section class="card grid gap-4 p-6 sm:grid-cols-2">
                    <h2 class="font-sans text-base font-semibold sm:col-span-2">Vidéo et visite virtuelle <span class="font-normal text-muted">(facultatif)</span></h2>
                    <div><label class="label" for="video">Lien YouTube</label><input id="video" name="video" type="url" value="{{ preg_match('~^https?://~', (string) $v('video', 'VIDEO')) ? $v('video', 'VIDEO') : '' }}" class="input" placeholder="https://youtu.be/…"></div>
                    <div><label class="label" for="videos_3d">Visite 3D (Klapty, Matterport…)</label><input id="videos_3d" name="videos_3d" type="url" value="{{ $v('videos_3d', 'VIDEOS_3D') }}" class="input"></div>
                </section>
            </div>

            {{-- Colonne d'action --}}
            <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                <div class="card p-5">
                    <button class="btn-roof w-full py-3" :disabled="envoi">
                        <span x-show="! envoi">{{ $edition ? 'Enregistrer les modifications' : 'Envoyer l\'annonce' }}</span>
                        <span x-show="envoi" x-cloak>Envoi des photos…</span>
                    </button>
                    <a href="{{ route('immo.pro.biens.index') }}" class="btn-ghost mt-2 w-full">Annuler</a>
                </div>
                <div class="rounded-xl bg-sand/70 p-5 text-sm text-ink/75">
                    <p class="font-semibold text-ink">Pour une annonce qui se démarque</p>
                    <ul class="mt-2 list-disc space-y-1 pl-4">
                        <li>des photos lumineuses, prises de jour, à l'horizontale ;</li>
                        <li>un prix affiché (les annonces sans prix sont moins consultées) ;</li>
                        <li>l'emplacement sur la carte.</li>
                    </ul>
                </div>
            </aside>
        </form>

        @if ($edition)
            @foreach ($bien->photos as $photo)
                <form id="supprimer-photo-{{ $photo->ID }}" method="POST" action="{{ route('immo.pro.biens.photos.destroy', [$bien->ID, $photo->ID]) }}" class="hidden">@csrf @method('DELETE')</form>
            @endforeach

            <form method="POST" action="{{ route('immo.pro.biens.destroy', $bien->ID) }}" class="mt-10 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-danger/20 bg-red-50/50 p-5"
                  onsubmit="return confirm('Supprimer définitivement cette annonce et toutes ses photos ?')">
                @csrf @method('DELETE')
                <p class="text-sm"><span class="font-semibold">Supprimer l'annonce</span> — la fiche et toutes ses photos seront effacées définitivement.</p>
                <button class="btn-danger btn-sm"><x-icon name="trash" class="size-3.5" /> Supprimer</button>
            </form>
        @endif
    </div>
@endsection
