@extends('layouts.admin')
@section('title', $entreprise->exists ? $entreprise->TITRE : 'Nouvelle entreprise')

@php
    $v = fn ($champ, $defaut = null) => old($champ, $defaut);
    $onglets = ['infos' => 'Identité', 'contacts' => 'Contacts & localisation', 'presentation' => 'Présentation'];
    if ($entreprise->exists) {
        $onglets += ['thematiques' => 'Rubriques ('.$thematiques->count().')', 'galerie' => 'Galerie ('.$photos->count().')', 'departements' => 'Services ('.$departements->count().')'];
    }
    $ongletInitial = request('onglet', $errors->hasAny(['tel_1', 'email_1', 'email_2', 'latitude', 'longitude']) ? 'contacts' : 'infos');
@endphp

@section('content')
<div x-data="{ onglet: @js(array_key_exists($ongletInitial, $onglets) ? $ongletInitial : 'infos') }">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.entreprises.index') }}" class="text-sm text-muted hover:text-ink">← Toutes les entreprises</a>
        @if ($entreprise->exists)
            <div class="flex gap-2">
                @if ($entreprise->LIEN)<a href="{{ url('/details/'.$entreprise->LIEN) }}" target="_blank" class="btn-line btn-sm"><x-icon name="external" class="size-3.5" /> Voir la fiche publique</a>@endif
                <x-admin.delete-button :action="route('admin.entreprises.destroy', $entreprise->ID)" label="Supprimer" :message="'Supprimer définitivement « '.$entreprise->TITRE.' » et toutes ses images ?'" />
            </div>
        @endif
    </div>

    <nav class="mb-4 flex flex-wrap gap-1 border-b border-line">
        @foreach ($onglets as $cle => $label)
            <button type="button" @click="onglet = '{{ $cle }}'" :class="onglet === '{{ $cle }}' ? 'border-ink text-ink' : 'border-transparent text-muted hover:text-ink'" class="-mb-px border-b-2 px-3 py-2 text-sm font-medium">{{ $label }}</button>
        @endforeach
    </nav>

    {{-- Formulaire principal (3 premiers onglets) --}}
    <form method="POST" enctype="multipart/form-data" action="{{ $entreprise->exists ? route('admin.entreprises.update', $entreprise->ID) : route('admin.entreprises.store') }}"
          x-show="['infos','contacts','presentation'].includes(onglet)">
        @csrf
        @if ($entreprise->exists) @method('PUT') @endif
        @if ($pourPharmacie)<input type="hidden" name="ajouter_pharmacie" value="1">@endif

        <div class="card grid gap-4 p-5 md:grid-cols-2" x-show="onglet === 'infos'">
            <div class="md:col-span-2"><label class="label">Raison sociale *</label><input name="titre" value="{{ $v('titre', $entreprise->TITRE) }}" class="input" required></div>
            <div><label class="label">Sigle</label><input name="sigle" value="{{ $v('sigle', $entreprise->SIGLE) }}" class="input"></div>
            <div><label class="label">Type *</label>
                <select name="type_id" class="input">@foreach ($types as $t)<option value="{{ $t->ID }}" @selected($v('type_id', $entreprise->TYPE_ID) == $t->ID)>{{ $t->DESIGNATION }}</option>@endforeach</select>
            </div>
            <div><label class="label">Commune</label>
                <select name="commune_id" class="input"><option value="">—</option>@foreach ($communes as $c)<option value="{{ $c->ID }}" @selected($v('commune_id', $entreprise->COMMUNE_ID) == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach</select>
            </div>
            <div><label class="label">Forme juridique</label><input name="forme_juridique" value="{{ $v('forme_juridique', $annuaire->FORME_JURIDIQUE) }}" class="input" placeholder="SARL, SA…"></div>
            <div class="md:col-span-2"><label class="label">Activités (mots utilisés par la recherche)</label><textarea name="activite" rows="2" class="input">{{ $v('activite', $entreprise->ACTIVITE) }}</textarea></div>

            <fieldset class="rounded-lg border border-line p-4 md:col-span-2">
                <legend class="px-1 text-sm font-semibold">Visibilité</legend>
                <div class="grid gap-4 sm:grid-cols-4">
                    <div><label class="label">Client Pratik</label>
                        <select name="client" class="input"><option value="NON" @selected($v('client', $annuaire->CLIENT ?? 'NON') !== 'OUI')>Non</option><option value="OUI" @selected($v('client', $annuaire->CLIENT) === 'OUI')>Oui (fiche pro)</option></select>
                    </div>
                    <div><label class="label">Priorité</label><input type="number" min="0" max="99" name="priorite" value="{{ $v('priorite', $entreprise->PRIORITE ?? 0) }}" class="input"></div>
                    <label class="flex items-center gap-2 pt-6 text-sm"><input type="hidden" name="vip" value="0"><input type="checkbox" name="vip" value="1" @checked($v('vip', $annuaire->VIP)) class="rounded border-line"> <span><strong>VIP</strong> <span class="text-muted">— couronne « TOP 12 », en tête de sa rubrique</span></span></label>
                    <label class="flex items-center gap-2 pt-6 text-sm"><input type="hidden" name="top10" value="0"><input type="checkbox" name="top10" value="1" @checked($v('top10', $annuaire->TOP10)) class="rounded border-line"> Top 10</label>
                    <label class="flex items-center gap-2 text-sm"><input type="hidden" name="activation" value="0"><input type="checkbox" name="activation" value="1" @checked($v('activation', $entreprise->ACTIVATION ?? 1)) class="rounded border-line"> Fiche active</label>
                    <div class="sm:col-span-3"><label class="label">Date de souscription</label><input type="date" name="date_souscription" value="{{ $v('date_souscription', $entreprise->DATE_SOUSCRIPTION) }}" class="input w-48"></div>
                </div>
            </fieldset>

            <div class="md:col-span-2 flex flex-wrap items-start gap-5 rounded-lg border border-line p-4">
                <x-logo-tile :src="$annuaire->logo_url" :name="$entreprise->TITRE" size="size-24" />
                <div class="flex-1 space-y-2">
                    <label class="label">Logo</label>
                    <input type="file" name="logo" accept="image/*" class="block text-sm">
                    <p class="text-xs text-muted">JPG, PNG ou WebP, 4 Mo max. Le précédent logo est supprimé du serveur.</p>
                    @if ($annuaire->LOGO)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="supprimer_logo" value="1" class="rounded border-line"> Retirer le logo actuel</label>@endif
                </div>
            </div>

            <div class="md:col-span-2" x-data="siegeLookup(@js($siege ? ['id' => $siege->ID, 'titre' => $siege->TITRE] : null))">
                <label class="label">Siège (si cette fiche est une succursale)</label>
                <input type="hidden" name="siege_id" :value="choix?.id ?? ''">
                <div class="flex gap-2" x-show="choix" x-cloak>
                    <span class="tag" x-text="choix?.titre"></span>
                    <button type="button" class="text-xs text-danger" @click="choix = null">retirer</button>
                </div>
                <div x-show="!choix" class="relative">
                    <input type="text" x-model="q" @input.debounce.300ms="chercher()" class="input" placeholder="Rechercher le siège…">
                    <ul x-show="resultats.length" class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md border border-line bg-white shadow-lg">
                        <template x-for="r in resultats" :key="r.id">
                            <li><button type="button" class="w-full px-3 py-2 text-left text-sm hover:bg-sand" @click="choix = r; resultats = []" x-text="r.titre + (r.commune ? ' — ' + r.commune : '')"></button></li>
                        </template>
                    </ul>
                </div>
            </div>

            @if ($entreprise->exists)
                <div class="md:col-span-2"><label class="label">Lien de la fiche (URL)</label>
                    <div class="flex items-center gap-1 text-sm"><span class="text-muted">/details/</span><input name="lien" value="{{ $v('lien', $entreprise->LIEN) }}" class="input"></div>
                    <p class="mt-1 text-xs text-amber-deep">Évitez de le modifier : l'ancienne adresse ne serait plus trouvée par Google.</p>
                </div>
            @endif
        </div>

        <div class="card grid gap-4 p-5 md:grid-cols-4" x-show="onglet === 'contacts'" x-cloak>
            <div><label class="label">Téléphone fixe 1</label><input name="tel_1" value="{{ $v('tel_1', $annuaire->TEL_1) }}" class="input"></div>
            <div><label class="label">Téléphone fixe 2</label><input name="tel_2" value="{{ $v('tel_2', $annuaire->TEL_2) }}" class="input"></div>
            <div><label class="label">Mobile 1</label><input name="cel_1" value="{{ $v('cel_1', $annuaire->CEL_1) }}" class="input"></div>
            <div><label class="label">Mobile 2</label><input name="cel_2" value="{{ $v('cel_2', $annuaire->CEL_2) }}" class="input"></div>
            <div><label class="label">WhatsApp</label><input name="whatsapp" value="{{ $v('whatsapp', $annuaire->WHATSAPP) }}" class="input"></div>
            <div><label class="label">Fax</label><input name="fax" value="{{ $v('fax', $annuaire->FAX) }}" class="input"></div>
            <div><label class="label">Service client</label><input name="service_client" value="{{ $v('service_client', $annuaire->SERVICE_CLIENT) }}" class="input"></div>
            <div><label class="label">Boîte postale</label><input name="bp" value="{{ $v('bp', $annuaire->BP) }}" class="input"></div>
            <div class="md:col-span-2"><label class="label">E-mail principal</label><input type="email" name="email_1" value="{{ $v('email_1', $annuaire->EMAIL_1) }}" class="input"></div>
            <div class="md:col-span-2"><label class="label">E-mail secondaire</label><input type="email" name="email_2" value="{{ $v('email_2', $annuaire->EMAIL_2) }}" class="input"></div>
            <div class="md:col-span-2"><label class="label">Site web</label><input name="site" value="{{ $v('site', $annuaire->SITE) }}" class="input" placeholder="www.exemple.ci"></div>
            <div class="md:col-span-2"><label class="label">Adresse</label><input name="adresse" value="{{ $v('adresse', $annuaire->ADRESSE) }}" class="input"></div>
            <div class="md:col-span-4"><label class="label">Indications d'accès</label><textarea name="localisation" rows="2" class="input">{{ $v('localisation', $annuaire->LOCALISATION) }}</textarea></div>
            <div class="md:col-span-2"><label class="label">Lien Google Maps</label><input name="geolocalisation" value="{{ $v('geolocalisation', $annuaire->GEOLOCALISATION) }}" class="input"></div>
            <div><label class="label">Latitude</label><input name="latitude" value="{{ $v('latitude', $annuaire->LATITUDE) }}" class="input" placeholder="5.3484"></div>
            <div><label class="label">Longitude</label><input name="longitude" value="{{ $v('longitude', $annuaire->LONGITUDE) }}" class="input" placeholder="-4.0305"></div>
            <p class="text-xs text-muted md:col-span-4">Latitude et longitude permettent d'afficher la carte et de proposer la pharmacie dans « autour de moi ».</p>
            <div><label class="label">Facebook</label><input name="facebook" value="{{ $v('facebook', $annuaire->FACEBOOK) }}" class="input"></div>
            <div><label class="label">Instagram</label><input name="instagram" value="{{ $v('instagram', $annuaire->INSTAGRAM) }}" class="input"></div>
            <div><label class="label">LinkedIn</label><input name="linkedin" value="{{ $v('linkedin', $annuaire->LINKEDIN) }}" class="input"></div>
            <div><label class="label">X (Twitter)</label><input name="tweeter" value="{{ $v('tweeter', $annuaire->TWEETER) }}" class="input"></div>
        </div>

        <div class="card grid gap-4 p-5 md:grid-cols-2" x-show="onglet === 'presentation'" x-cloak>
            <div class="md:col-span-2"><label class="label">Résumé (affiché dans les listes)</label><textarea name="resume" rows="2" maxlength="1500" class="input">{{ $v('resume', $annuaire->RESUME) }}</textarea></div>
            <div class="md:col-span-2"><label class="label">Description</label><textarea name="description" rows="8" class="input">{{ $v('description', $annuaire->DESCRIPTION) }}</textarea></div>
            <div class="md:col-span-2"><label class="label">Mots-clés</label><textarea name="mot_cle" rows="2" class="input">{{ $v('mot_cle', $annuaire->MOT_CLE) }}</textarea></div>
            <div><label class="label">Nature</label>
                <select name="nature_id" class="input"><option value="">—</option>@foreach ($natures as $n)<option value="{{ $n->ID }}" @selected($v('nature_id', $annuaire->NATURE_ID) == $n->ID)>{{ $n->DESIGNATION }}</option>@endforeach</select>
            </div>
            <div><label class="label">Vidéo (lien YouTube)</label><input name="video" value="{{ $v('video', $annuaire->VIDEO) }}" class="input"></div>
            <div class="md:col-span-2"><label class="label">Visite 3D (lien)</label><input name="video_3d" value="{{ $v('video_3d', $annuaire->VIDEO_3D) }}" class="input"></div>
        </div>

        <div class="sticky bottom-0 mt-4 flex justify-end gap-2 border-t border-line bg-[#f6f4ef]/95 py-3">
            <a href="{{ route('admin.entreprises.index') }}" class="btn-ghost">Annuler</a>
            <button class="btn-ink">{{ $entreprise->exists ? 'Enregistrer' : 'Créer l\'entreprise' }}</button>
        </div>
    </form>

    @if ($entreprise->exists)
        {{-- Rubriques de l'annuaire --}}
        <section class="card p-5" x-show="onglet === 'thematiques'" x-cloak>
            <form method="POST" action="{{ route('admin.entreprises.thematiques.store', $entreprise->ID) }}" class="flex flex-wrap items-end gap-3"
                  x-data="{ categorie: '', sous: @js($categories->mapWithKeys(fn ($c) => [$c->ID => $c->sousCategories->map(fn ($s) => ['id' => $s->ID, 'nom' => $s->DESIGNATION])])) }">
                @csrf
                <div><label class="label">Catégorie</label>
                    <select x-model="categorie" class="input w-64"><option value="">—</option>@foreach ($categories as $c)<option value="{{ $c->ID }}">{{ $c->DESIGNATION }}</option>@endforeach</select>
                </div>
                <div><label class="label">Sous-catégorie</label>
                    <select name="sous_categorie_id" class="input w-72" required :disabled="!categorie">
                        <option value="">—</option>
                        <template x-for="s in (sous[categorie] || [])" :key="s.id"><option :value="s.id" x-text="s.nom"></option></template>
                    </select>
                </div>
                <button class="btn-ink"><x-icon name="plus" class="size-4" /> Ajouter</button>
            </form>
            <ul class="mt-5 divide-y divide-line rounded-lg border border-line">
                @forelse ($thematiques as $t)
                    <li class="flex items-center justify-between px-3 py-2 text-sm">
                        <span><span class="text-muted">{{ $t->categorie?->DESIGNATION }} ›</span> {{ $t->sousCategorie?->DESIGNATION ?? 'Sous-catégorie #'.$t->SOUS_CATEGORIE_ID }}</span>
                        <x-admin.delete-button :action="route('admin.entreprises.thematiques.destroy', [$entreprise->ID, $t->ID])" message="Retirer cette rubrique ?" />
                    </li>
                @empty
                    <li class="px-3 py-4 text-sm text-muted">Aucune rubrique : l'entreprise n'apparaît dans aucune liste de l'annuaire.</li>
                @endforelse
            </ul>
        </section>

        {{-- Galerie --}}
        <section class="card p-5" x-show="onglet === 'galerie'" x-cloak>
            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.entreprises.photos.store', $entreprise->ID) }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div><label class="label">Ajouter des photos</label><input type="file" name="photos[]" multiple accept="image/*" class="text-sm" required></div>
                <button class="btn-ink"><x-icon name="upload" class="size-4" /> Envoyer</button>
            </form>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                @forelse ($photos as $photo)
                    <figure class="group relative overflow-hidden rounded-lg border border-line bg-sand">
                        <img src="{{ $photo->url }}" alt="" class="aspect-square w-full object-cover" loading="lazy">
                        <x-admin.delete-button class="absolute top-1.5 right-1.5" :action="route('admin.entreprises.photos.destroy', [$entreprise->ID, $photo->ID])" message="Supprimer cette photo du serveur ?" />
                    </figure>
                @empty
                    <p class="col-span-full text-sm text-muted">Aucune photo.</p>
                @endforelse
            </div>
        </section>

        {{-- Services / départements --}}
        <section class="card p-5" x-show="onglet === 'departements'" x-cloak>
            <form method="POST" action="{{ route('admin.entreprises.departements.store', $entreprise->ID) }}" class="grid gap-3 sm:grid-cols-5 sm:items-end">
                @csrf
                <div class="sm:col-span-2"><label class="label">Service</label><input name="libelle" class="input" required placeholder="Service commercial"></div>
                <div><label class="label">Téléphone</label><input name="tel" class="input"></div>
                <div><label class="label">E-mail</label><input name="email" type="email" class="input"></div>
                <button class="btn-ink"><x-icon name="plus" class="size-4" /> Ajouter</button>
            </form>
            <table class="table-admin mt-5">
                <tbody>
                    @forelse ($departements as $d)
                        <tr><td class="font-medium">{{ $d->LIBELLE }}</td><td>{{ $d->TEL }} {{ $d->CEL }}</td><td>{{ $d->EMAIL }}</td>
                            <td class="text-right"><x-admin.delete-button :action="route('admin.entreprises.departements.destroy', [$entreprise->ID, $d->ID])" /></td></tr>
                    @empty
                        <tr><td class="text-muted">Aucun service.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('siegeLookup', (initial) => ({
            choix: initial, q: '', resultats: [],
            async chercher() {
                if (this.q.length < 2) { this.resultats = []; return; }
                const r = await fetch(@js(route('admin.entreprises.lookup')) + '?q=' + encodeURIComponent(this.q), { headers: { Accept: 'application/json' } });
                this.resultats = r.ok ? await r.json() : [];
            },
        }));
    });
</script>
@endpush
@endsection
