@extends('layouts.admin')
@section('title', $bien->entreprise?->TITRE ?? 'Bien #'.$bien->ID)

@php($v = fn ($c, $d = null) => old($c, $d))

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('admin.biens.index') }}" class="text-sm text-muted hover:text-ink">← Tous les biens</a>
    <div class="flex items-center gap-2 text-sm">
        <span class="text-muted">Publié par {{ $bien->auteur?->name ?? '—' }} le {{ $bien->DATE_LOG }}</span>
        @if ($bien->entreprise?->LIEN)<a href="{{ url('/immobilier/proprietes-'.$bien->entreprise->LIEN) }}" target="_blank" class="btn-line btn-sm"><x-icon name="external" class="size-3.5" /> Voir</a>@endif
        <x-admin.delete-button :action="route('admin.biens.destroy', $bien->ID)" label="Supprimer" message="Supprimer ce bien et toutes ses photos ?" />
    </div>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.biens.update', $bien->ID) }}" class="grid gap-6 xl:grid-cols-3">
    @csrf @method('PUT')
    <div class="space-y-6 xl:col-span-2">
        <section class="card grid gap-4 p-5 md:grid-cols-3">
            <div class="md:col-span-3"><label class="label">Titre de l'annonce</label><input name="titre" value="{{ $v('titre', $bien->entreprise?->TITRE) }}" class="input" required></div>
            <div><label class="label">Type d'offre</label><select name="type_id" class="input">@foreach ($types as $t)<option value="{{ $t->ID }}" @selected($v('type_id', $bien->TYPE_IMMOBILIER_ID) == $t->ID)>{{ $t->DESIGNATION }}</option>@endforeach</select></div>
            <div><label class="label">Catégorie</label><select name="categorie_id" class="input"><option value="">—</option>@foreach ($categories as $c)<option value="{{ $c->ID }}" @selected($v('categorie_id', $bien->CATEGORIE_IMMOBILIER_ID) == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach</select></div>
            <div><label class="label">Commune</label><select name="commune_id" class="input"><option value="">—</option>@foreach ($communes as $c)<option value="{{ $c->ID }}" @selected($v('commune_id', $bien->entreprise?->COMMUNE_ID) == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach</select></div>
            <div><label class="label">Prix (FCFA)</label><input name="prix" value="{{ $v('prix', $bien->PRIX) }}" class="input" inputmode="numeric"></div>
            <div><label class="label">Prix au m²</label><input name="prix_m2" value="{{ $v('prix_m2', $bien->PRIX_M2) }}" class="input" inputmode="numeric"></div>
            <div><label class="label">Surface (m²)</label><input name="surface" value="{{ $v('surface', $bien->SURFACE) }}" class="input"></div>
            <div><label class="label">Pièces</label><input type="number" min="0" name="pieces" value="{{ $v('pieces', $bien->PIECES) }}" class="input"></div>
            <div><label class="label">Chambres</label><input type="number" min="0" name="chambre" value="{{ $v('chambre', $bien->CHAMBRE) }}" class="input"></div>
            <div><label class="label">Salles de bain</label><input type="number" min="0" name="salle_bain" value="{{ $v('salle_bain', $bien->SALLE_BAIN) }}" class="input"></div>
            <div><label class="label">Salons</label><input type="number" min="0" name="salons" value="{{ $v('salons', $bien->SALONS) }}" class="input"></div>
            <div><label class="label">État</label><input name="etat_bien" value="{{ $v('etat_bien', $bien->ETAT_BIEN) }}" class="input" placeholder="Neuf, bon état…"></div>
        </section>

        <section class="card p-5">
            <h2 class="mb-3 font-sans text-sm font-semibold">Équipements</h2>
            @php($coches = old('equipements', collect(\App\Models\Immobilier::EQUIPEMENTS)->keys()->filter(fn ($c) => strtoupper((string) $bien->{$c}) === 'OUI')->all()))
            <div class="grid gap-2 sm:grid-cols-3">
                @foreach (\App\Models\Immobilier::EQUIPEMENTS as $col => $label)
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="equipements[]" value="{{ $col }}" @checked(in_array($col, $coches, true)) class="rounded border-line"> {{ $label }}</label>
                @endforeach
            </div>
        </section>

        <section class="card grid gap-4 p-5 md:grid-cols-2">
            <div class="md:col-span-2"><label class="label">Description</label><textarea name="description" rows="7" class="input">{{ $v('description', $bien->DESCRIPTION) }}</textarea></div>
            <div class="md:col-span-2"><label class="label">Adresse</label><input name="adresse" value="{{ $v('adresse', $bien->ADRESSE) }}" class="input"></div>
            <div><label class="label">Latitude</label><input name="latitude" value="{{ $v('latitude', $bien->LATITUDE) }}" class="input"></div>
            <div><label class="label">Longitude</label><input name="longitude" value="{{ $v('longitude', $bien->LONGITUDE) }}" class="input"></div>
            <div class="md:col-span-2"><label class="label">Lien Google Maps</label><input name="google_maps" value="{{ $v('google_maps', $bien->GOOGLE_MAPS) }}" class="input"></div>
            <div><label class="label">Vidéo (YouTube)</label><input name="video" value="{{ $v('video', $bien->VIDEO) }}" class="input"></div>
            <div><label class="label">Visite 3D</label><input name="videos_3d" value="{{ $v('videos_3d', $bien->VIDEOS_3D) }}" class="input"></div>
            <div><label class="label">Mots-clés</label><input name="mots_cles" value="{{ $v('mots_cles', $bien->MOTS_CLES) }}" class="input"></div>
            <div><label class="label">Meta description</label><input name="metas" value="{{ $v('metas', $bien->METAS) }}" class="input"></div>
        </section>

        <section class="card p-5">
            <h2 class="mb-3 font-sans text-sm font-semibold">Galerie ({{ $bien->photos->count() }})</h2>
            <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                @foreach ($bien->photos as $photo)
                    <figure class="relative overflow-hidden rounded-lg border border-line bg-sand">
                        <img src="{{ $photo->url }}" alt="" class="aspect-square w-full object-cover" loading="lazy">
                        <button form="suppr-photo-{{ $photo->ID }}" class="absolute top-1 right-1 rounded bg-white/90 p-1 text-danger shadow" title="Supprimer"><x-icon name="trash" class="size-3.5" /></button>
                    </figure>
                @endforeach
            </div>
            <label class="label mt-4">Ajouter des photos</label>
            <input type="file" name="photos[]" multiple accept="image/*" class="text-sm">
        </section>
    </div>

    <aside class="space-y-6">
        <section class="card space-y-4 p-5">
            <h2 class="font-sans text-sm font-semibold">Publication</h2>
            <div><label class="label">Statut</label>
                <select name="validation" class="input">
                    <option value="NON" @selected($v('validation', $bien->VALIDATION) !== 'OUI')>En attente (non visible)</option>
                    <option value="OUI" @selected($v('validation', $bien->VALIDATION) === 'OUI')>Validé (en ligne)</option>
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="flash" value="1" @checked($v('flash', strtoupper((string) $bien->FLASH) === 'OUI')) class="rounded border-line"> Mise en avant (flash)</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="luxe" value="1" @checked($v('luxe', strtoupper((string) $bien->LUXE) === 'OUI')) class="rounded border-line"> Immobilier de luxe</label>
        </section>

        <section class="card space-y-3 p-5">
            <h2 class="font-sans text-sm font-semibold">Photo principale</h2>
            <img src="{{ $bien->image_url }}" alt="" class="aspect-[4/3] w-full rounded-lg bg-sand object-cover">
            <input type="file" name="illustration" accept="image/*" class="text-sm">
            <p class="text-xs text-muted">L'ancienne photo est supprimée du serveur.</p>
        </section>

        <section class="card space-y-3 p-5">
            <h2 class="font-sans text-sm font-semibold">Contact vendeur (optionnel)</h2>
            <input name="v_nom" value="{{ $v('v_nom', $bien->V_NOM) }}" class="input" placeholder="Nom">
            <input name="v_tel" value="{{ $v('v_tel', $bien->V_TEL) }}" class="input" placeholder="Téléphone">
            <input name="v_cel" value="{{ $v('v_cel', $bien->V_CEL) }}" class="input" placeholder="Mobile">
            <input name="v_whatsapp" value="{{ $v('v_whatsapp', $bien->V_WHATSAPP) }}" class="input" placeholder="WhatsApp">
            <input name="v_email" type="email" value="{{ $v('v_email', $bien->V_EMAIL) }}" class="input" placeholder="E-mail">
        </section>

        <button class="btn-ink w-full py-3">Enregistrer</button>
    </aside>
</form>

@foreach ($bien->photos as $photo)
    <form id="suppr-photo-{{ $photo->ID }}" method="POST" action="{{ route('admin.biens.photos.destroy', [$bien->ID, $photo->ID]) }}" onsubmit="return confirm('Supprimer cette photo du serveur ?')" class="hidden">@csrf @method('DELETE')</form>
@endforeach
@endsection
