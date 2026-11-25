@extends('layouts.admin')
@section('title', 'Vues affichées')

@php
    $nf = fn ($n) => number_format((int) $n, 0, ',', ' ');
    $groupes = collect($perimetres)->except('fiche')->groupBy(fn ($p) => $p[0], preserveKeys: true);
@endphp

@section('content')
<p class="mb-6 max-w-3xl text-sm text-muted">
    Les vues ajoutées ici s'ajoutent aux vues réelles sur le site, dans l'espace annonceur et dans l'application.
    Les vues réelles ne sont jamais modifiées : « remettre à zéro » retire uniquement les vues ajoutées.
</p>

{{-- État actuel par périmètre --}}
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach ($resumes as $cle => $r)
        <div class="card p-4">
            <p class="text-xs font-semibold tracking-wide text-muted uppercase">{{ $perimetres[$cle][0] }}</p>
            <p class="mt-1 text-sm font-medium">{{ $perimetres[$cle][1] }}</p>
            <p class="mt-3 font-serif text-2xl font-semibold">{{ $nf($r['reelles'] + $r['ajoutees']) }}</p>
            <p class="text-xs text-muted">{{ $nf($r['fiches']) }} fiches · {{ $nf($r['reelles']) }} réelles + <span class="text-amber-deep">{{ $nf($r['ajoutees']) }} ajoutées</span></p>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-5">
    {{-- Augmentation en masse --}}
    <form method="POST" action="{{ route('admin.vues.ajouter') }}" class="card space-y-5 p-5 xl:col-span-3"
          x-data="{ perimetre: @js(old('perimetre', 'annuaire_vip')), aleatoire: @js((bool) old('maximum')) }">
        @csrf
        <h2 class="font-sans text-base font-semibold">Augmenter les vues d'un groupe</h2>

        <fieldset class="space-y-4">
            @foreach ($groupes as $groupe => $items)
                <div>
                    <legend class="mb-2 text-xs font-semibold tracking-wide text-muted uppercase">{{ $groupe }}</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($items as $cle => [, $libelle])
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-line px-3 py-2.5 text-sm has-[:checked]:border-ink has-[:checked]:bg-sand/60">
                                <input type="radio" name="perimetre" value="{{ $cle }}" x-model="perimetre" class="border-line">
                                {{ $libelle }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </fieldset>

        <div x-show="perimetre === 'annuaire_categorie'" x-cloak>
            <label class="label">Catégorie</label>
            <select name="categorie" class="input" :disabled="perimetre !== 'annuaire_categorie'">
                <option value="">Choisir…</option>
                @foreach ($categories as $c)<option value="{{ $c->ID }}" @selected(old('categorie') == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach
            </select>
        </div>
        <div x-show="perimetre === 'immo_annonceur'" x-cloak>
            <label class="label">Annonceur</label>
            <select name="annonceur" class="input" :disabled="perimetre !== 'immo_annonceur'">
                <option value="">Choisir…</option>
                @foreach ($annonceurs as $a)<option value="{{ $a->id }}" @selected(old('annonceur') == $a->id)>{{ $a->name }}</option>@endforeach
            </select>
        </div>

        <div class="rounded-lg bg-sand/60 p-4">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" x-model="aleatoire" class="rounded border-line">
                Nombre différent pour chaque fiche (plus naturel)
            </label>
            <div class="mt-3 grid grid-cols-2 gap-3">
                <div>
                    <label class="label" x-text="aleatoire ? 'Minimum' : 'Vues à ajouter à chaque fiche'"></label>
                    <input type="number" name="minimum" min="1" max="100000" value="{{ old('minimum', 50) }}" required class="input">
                </div>
                <div x-show="aleatoire" x-cloak>
                    <label class="label">Maximum</label>
                    <input type="number" name="maximum" min="1" max="100000" value="{{ old('maximum', 150) }}" class="input" :disabled="!aleatoire">
                </div>
            </div>
        </div>

        <button class="btn-amber w-full" onclick="return confirm('Ajouter ces vues à toutes les fiches du groupe choisi ?')">
            <x-icon name="eye" class="size-4" /> Ajouter les vues
        </button>
    </form>

    {{-- Remise à zéro --}}
    <div class="space-y-6 xl:col-span-2">
        <form method="POST" action="{{ route('admin.vues.zero') }}" class="card space-y-4 p-5" x-data="{ perimetre: 'annuaire_vip' }">
            @csrf
            <h2 class="font-sans text-base font-semibold">Revenir aux vues réelles</h2>
            <select name="perimetre" x-model="perimetre" class="input">
                @foreach (collect($perimetres)->except('fiche') as $cle => [$groupe, $libelle])
                    <option value="{{ $cle }}">{{ $groupe }} — {{ $libelle }}</option>
                @endforeach
            </select>
            <select name="categorie" class="input" x-show="perimetre === 'annuaire_categorie'" x-cloak :disabled="perimetre !== 'annuaire_categorie'">
                @foreach ($categories as $c)<option value="{{ $c->ID }}">{{ $c->DESIGNATION }}</option>@endforeach
            </select>
            <select name="annonceur" class="input" x-show="perimetre === 'immo_annonceur'" x-cloak :disabled="perimetre !== 'immo_annonceur'">
                @foreach ($annonceurs as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
            </select>
            <button class="btn-line w-full" onclick="return confirm('Retirer les vues ajoutées de ce groupe ? Les vues réelles sont conservées.')">Remettre les vues ajoutées à zéro</button>
        </form>

        <div class="card">
            <h2 class="border-b border-line px-5 py-3 font-sans text-base font-semibold">Dernières opérations</h2>
            @forelse ($historique as $h)
                <div class="border-b border-line/70 px-5 py-2.5 text-sm last:border-0">
                    <p class="flex justify-between gap-3">
                        <span class="font-medium">{{ $h->operation === 'ajout' ? ($h->minimum === $h->maximum ? '+'.$h->minimum : '+'.$h->minimum.' à +'.$h->maximum) : 'Remise à zéro' }}</span>
                        <span class="text-xs text-muted">{{ \Carbon\Carbon::parse($h->created_at)->locale('fr')->isoFormat('D MMM YYYY, HH:mm') }}</span>
                    </p>
                    <p class="text-muted">{{ $h->libelle }} · {{ $nf($h->fiches) }} fiche(s) · {{ $h->auteur }}</p>
                </div>
            @empty
                <p class="px-5 py-4 text-sm text-muted">Aucune opération pour l'instant.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Au cas par cas --}}
<section class="card mt-6">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
        <h2 class="font-sans text-base font-semibold">Au cas par cas</h2>
        <form method="GET" class="flex gap-2">
            <input type="search" name="q" value="{{ $q }}" placeholder="Nom de l'entreprise ou de l'annonce, ou n° de fiche" class="input w-80 py-2">
            <button class="btn-ink btn-sm">Chercher</button>
        </form>
    </div>
    @if ($q !== '' && $resultats->isEmpty())
        <p class="px-5 py-6 text-sm text-muted">Aucune fiche trouvée pour « {{ $q }} ».</p>
    @elseif ($resultats->isNotEmpty())
        <table class="table-admin">
            <thead><tr><th>Fiche</th><th>Type</th><th class="text-right">Réelles</th><th class="text-right">Ajoutées</th><th class="text-right">Affichées</th><th class="w-72">Ajouter</th></tr></thead>
            <tbody>
                @foreach ($resultats as $e)
                    <tr>
                        <td>
                            <a href="{{ $e->TYPE_ID == config('pratik.types.immobilier') ? route('immo.bien', $e->LIEN) : route('annuaire.fiche', $e->LIEN) }}" target="_blank" class="font-medium hover:text-amber-deep">{{ $e->TITRE }}</a>
                            <span class="block text-xs text-muted">n° {{ $e->ID }}{{ $e->commune ? ' · '.$e->commune->DESIGNATION : '' }}</span>
                        </td>
                        <td class="text-xs">{{ $e->TYPE_ID == config('pratik.types.immobilier') ? 'Annonce' : 'Annuaire' }}</td>
                        <td class="text-right tabular-nums">{{ $nf($e->COMPTEUR) }}</td>
                        <td class="text-right tabular-nums text-amber-deep">{{ $nf($e->VUES_BONUS) }}</td>
                        <td class="text-right font-semibold tabular-nums">{{ $nf($e->vues_affichees) }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.vues.ajouter') }}" class="flex gap-1.5">
                                    @csrf
                                    <input type="hidden" name="perimetre" value="fiche">
                                    <input type="hidden" name="fiche" value="{{ $e->ID }}">
                                    <input type="number" name="minimum" min="1" max="100000" value="100" class="input w-24 py-1.5">
                                    <button class="btn-amber btn-sm">+ Ajouter</button>
                                </form>
                                @if ($e->VUES_BONUS)
                                    <form method="POST" action="{{ route('admin.vues.zero') }}">
                                        @csrf
                                        <input type="hidden" name="perimetre" value="fiche">
                                        <input type="hidden" name="fiche" value="{{ $e->ID }}">
                                        <button class="btn-ghost btn-sm" title="Retirer les vues ajoutées">Remettre à 0</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="px-5 py-6 text-sm text-muted">Cherchez une entreprise de l'annuaire ou une annonce pour ajuster ses vues individuellement.</p>
    @endif
</section>
@endsection
