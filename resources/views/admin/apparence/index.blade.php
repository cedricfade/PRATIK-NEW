@extends('layouts.admin')
@section('title', 'Apparence des pages d\'accueil')

@php
    $accent = $page === 'immobilier' ? '#f2b31b' : '#eaa945';
    $variables = $page === 'immobilier' ? ['{ventes}', '{locations}'] : ['{entreprises}', '{biens}', '{pharmacies}'];
@endphp

@section('content')
<div class="mb-5 flex flex-wrap items-center gap-2">
    @foreach (\App\Support\Hero::PAGES as $cle => $libelle)
        <a href="{{ route('admin.apparence.index', ['page' => $cle]) }}" @class([
            'rounded-md px-3 py-2 text-sm font-medium',
            'bg-ink text-white' => $page === $cle,
            'bg-white text-ink/70 ring-1 ring-line hover:text-ink' => $page !== $cle,
        ])>{{ $libelle }}</a>
    @endforeach
    <a href="{{ $page === 'immobilier' ? route('immo.home') : route('home') }}" target="_blank" class="ml-auto inline-flex items-center gap-1.5 text-sm text-muted hover:text-ink">
        Voir la page <x-icon name="external" class="size-3.5" />
    </a>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.apparence.update', $page) }}"
      x-data="{
          image: @js($imageUrl),
          imageDefaut: @js(asset($defauts['image'])),
          affichage: @js(old('affichage', $valeurs['affichage'] ?? 'photo')),
          voile: {{ (int) old('voile', $valeurs['voile'] ?? 60) }},
          cadrage: @js(old('cadrage', $valeurs['cadrage'] ?? 'center')),
          surtitre: @js(old('surtitre', $valeurs['surtitre'])),
          titre: @js(old('titre', $valeurs['titre'])),
          accent: @js(old('titre_accent', $valeurs['titre_accent'])),
          texte: @js(old('texte', $valeurs['texte'])),
          choisir(e) { const f = e.target.files[0]; if (f) { this.image = URL.createObjectURL(f); this.$refs.defaut && (this.$refs.defaut.checked = false); } },
      }"
      class="grid gap-6 xl:grid-cols-5">
    @csrf @method('PUT')

    {{-- Aperçu en direct --}}
    <div class="xl:col-span-3">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-line px-4 py-2.5 text-sm">
                <span class="font-semibold">Aperçu</span>
                <span class="text-muted" x-text="affichage === 'banniere' ? 'Bannière affichée telle quelle' : 'Photo + voile + titre'"></span>
            </div>
            <div class="relative isolate aspect-[16/7] overflow-hidden bg-ink">
                <img :src="image" alt="" class="absolute inset-0 -z-20 size-full object-cover" :class="{ top: 'object-top', center: 'object-center', bottom: 'object-bottom' }[cadrage]">
                <template x-if="affichage === 'photo'">
                    <div class="absolute inset-0 -z-10" :style="`background: linear-gradient(90deg, rgba(20,32,43,${Math.min(0.95, voile / 100 + 0.15)}) 0%, rgba(20,32,43,${voile / 100}) 55%, rgba(20,32,43,${voile / 100 * 0.55}) 100%)`"></div>
                </template>
                <div x-show="affichage === 'photo'" class="flex h-full flex-col justify-center p-6 sm:p-10">
                    <p class="text-[10px] font-semibold tracking-[0.14em] uppercase sm:text-xs" style="color: {{ $accent }}" x-text="surtitre"></p>
                    <p class="mt-2 max-w-md font-serif text-2xl leading-tight font-semibold text-white sm:text-4xl">
                        <span x-text="titre"></span>
                        <em class="font-medium" style="color: {{ $accent }}" x-text="accent"></em>
                    </p>
                    <p class="mt-2 max-w-md text-xs text-white/85 sm:text-sm" x-text="texte"></p>
                    <div class="mt-4 h-9 max-w-md rounded-lg bg-white/95"></div>
                </div>
            </div>
        </div>
        <p class="mt-3 text-sm text-muted">
            Variables remplacées par les chiffres réels : @foreach ($variables as $v)<code class="rounded bg-sand px-1">{{ $v }}</code> @endforeach
        </p>
    </div>

    {{-- Réglages --}}
    <div class="card space-y-5 p-5 xl:col-span-2">
        <div>
            <label class="label">Image de fond</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" @change="choisir" class="block w-full text-sm">
            <p class="mt-1 text-xs text-muted">Format paysage, idéalement 1920 × 720 px. JPG, PNG ou WebP, 6 Mo maximum.</p>
            @unless ($imageParDefaut)
                <label class="mt-2 flex items-center gap-2 text-sm">
                    <input type="checkbox" name="image_defaut" value="1" x-ref="defaut" @change="if ($event.target.checked) image = imageDefaut" class="rounded border-line">
                    Revenir à l'image par défaut
                </label>
            @endunless
        </div>

        <div>
            <label class="label">Image pour mobile <span class="font-normal text-muted">(facultatif)</span></label>
            @if ($imageMobileUrl)
                <div class="mb-2 flex items-center gap-3">
                    <img src="{{ $imageMobileUrl }}" alt="" class="h-12 w-20 rounded border border-line object-cover">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="supprimer_image_mobile" value="1" class="rounded border-line"> Retirer</label>
                </div>
            @endif
            <input type="file" name="image_mobile" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
            <p class="mt-1 text-xs text-muted">Utile pour les bannières avec texte : format plus haut, par ex. 848 × 453 px.</p>
        </div>

        <fieldset>
            <legend class="label">Affichage</legend>
            <div class="grid gap-2 sm:grid-cols-2">
                <label class="flex cursor-pointer gap-2 rounded-lg border border-line p-3 text-sm has-[:checked]:border-ink has-[:checked]:bg-sand/60">
                    <input type="radio" name="affichage" value="photo" x-model="affichage" class="mt-0.5 border-line">
                    <span><strong class="block">Photo + titre</strong><span class="text-muted">La photo est assombrie, le titre s'affiche par-dessus.</span></span>
                </label>
                <label class="flex cursor-pointer gap-2 rounded-lg border border-line p-3 text-sm has-[:checked]:border-ink has-[:checked]:bg-sand/60">
                    <input type="radio" name="affichage" value="banniere" x-model="affichage" class="mt-0.5 border-line">
                    <span><strong class="block">Bannière</strong><span class="text-muted">Image déjà composée (Noël, CAN…), affichée telle quelle.</span></span>
                </label>
            </div>
        </fieldset>

        <div>
            <label class="label">Partie de l'image à montrer</label>
            <div class="inline-flex rounded-lg bg-sand p-1 text-sm">
                @foreach (['top' => 'Haut', 'center' => 'Centre', 'bottom' => 'Bas'] as $val => $lib)
                    <label class="cursor-pointer rounded-md px-3 py-1.5 has-[:checked]:bg-white has-[:checked]:shadow-sm">
                        <input type="radio" name="cadrage" value="{{ $val }}" x-model="cadrage" class="sr-only"> {{ $lib }}
                    </label>
                @endforeach
            </div>
        </div>

        <div x-show="affichage === 'photo'" class="space-y-4">
            <div>
                <label class="label">Assombrissement de la photo : <span x-text="voile + ' %'"></span></label>
                <input type="range" name="voile" min="0" max="90" step="5" x-model.number="voile" class="w-full accent-ink">
            </div>
            <div><label class="label">Petit titre au-dessus</label><input name="surtitre" x-model="surtitre" class="input" maxlength="150"></div>
            <div><label class="label">Titre</label><input name="titre" x-model="titre" class="input" maxlength="150"></div>
            <div>
                <label class="label">Fin du titre en couleur du logo</label>
                <input name="titre_accent" x-model="accent" class="input" maxlength="100">
            </div>
            <div><label class="label">Texte sous le titre <span class="font-normal text-muted">(facultatif)</span></label><textarea name="texte" x-model="texte" rows="3" class="input" maxlength="400"></textarea></div>
        </div>

        @if ($page === 'accueil')
            <fieldset class="space-y-3 border-t border-line pt-5">
                <legend class="text-sm font-semibold">Chiffres sous l'en-tête</legend>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="chiffres_afficher" value="0">
                    <input type="checkbox" name="chiffres_afficher" value="1" @checked(old('chiffres_afficher', $chiffres['afficher'])) class="rounded border-line">
                    Afficher les chiffres (entreprises, annonces, pharmacies : calculés automatiquement)
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="label">Visiteurs</label><input name="chiffres_visiteurs" value="{{ old('chiffres_visiteurs', $chiffres['visiteurs']) }}" class="input" placeholder="+10 000"></div>
                    <div><label class="label">Libellé</label><input name="chiffres_visiteurs_libelle" value="{{ old('chiffres_visiteurs_libelle', $chiffres['visiteurs_libelle']) }}" class="input"></div>
                </div>
            </fieldset>
        @endif

        <button class="btn-ink w-full">Enregistrer</button>
    </div>
</form>
@endsection
