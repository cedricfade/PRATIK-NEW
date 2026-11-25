@extends('layouts.immo')

@php
    $communeChoisie = isset($filtres['commune']) ? $communes->firstWhere('ID', $filtres['commune']) : null;
    $categorieChoisie = isset($filtres['categorie']) ? $categories->firstWhere('ID', $filtres['categorie']) : null;
    $titrePage = trim(($categorieChoisie?->DESIGNATION ? $categorieChoisie->DESIGNATION.' — ' : '').$titre.($communeChoisie ? ' à '.$communeChoisie->DESIGNATION : ''));
    $intro = match ($transaction) {
        'location' => 'Appartements, villas, studios et bureaux à louer, publiés directement par les agences.',
        'luxe' => 'Villas d\'exception, résidences avec piscine et programmes haut de gamme.',
        default => 'Appartements, villas, terrains et immeubles à vendre, proposés par des agences, promoteurs et aménageurs.',
    };
    $avecFiltres = collect($filtres)->except(['tri', 'luxe'])->isNotEmpty() || ($transaction !== 'luxe' && isset($filtres['luxe']));
@endphp

@section('title', $titrePage)
@section('description', $biens->total().' annonces — '.$intro)
@if ($biens->currentPage() > 1 || $avecFiltres)
    @section('canonical', route($route))
@endif

@section('content')
    <x-page-header tone="immo" :title="$titrePage" :breadcrumbs="['Immobilier' => route('immo.home'), $titre => null]">
        {{ number_format($biens->total(), 0, ',', ' ') }} {{ \Illuminate\Support\Str::plural('annonce', $biens->total()) }}. {{ $intro }}
    </x-page-header>

    <div class="container-page grid gap-8 py-8 lg:grid-cols-[280px_1fr]" x-data="{ filtres: false, carte: false }">
        {{-- Filtres --}}
        <aside>
            <button type="button" @click="filtres = !filtres" class="btn-line w-full lg:hidden">
                <x-icon name="filter" class="size-4" /> Filtrer les annonces
                @if ($avecFiltres)<span class="rounded-full bg-roof px-1.5 text-xs">{{ collect($filtres)->except('tri')->count() }}</span>@endif
            </button>
            <form method="GET" action="{{ route($route) }}" :class="filtres ? 'block' : 'hidden lg:block'" class="card mt-3 space-y-4 p-5 lg:sticky lg:top-24 lg:mt-0">
                <div>
                    <label class="label" for="f-categorie">Type de bien</label>
                    <select id="f-categorie" name="categorie" class="input">
                        <option value="">Tous</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->ID }}" @selected(($filtres['categorie'] ?? null) == $c->ID)>{{ $c->DESIGNATION }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="f-commune">Commune</label>
                    <select id="f-commune" name="commune" class="input">
                        <option value="">Toutes</option>
                        @foreach ($communes as $c)
                            <option value="{{ $c->ID }}" @selected(($filtres['commune'] ?? null) == $c->ID)>{{ $c->DESIGNATION }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <span class="label">Budget (FCFA)</span>
                    <div class="grid grid-cols-2 gap-2">
                        <input name="prix_min" inputmode="numeric" placeholder="Min" aria-label="Prix minimum" value="{{ isset($filtres['prix_min']) ? number_format($filtres['prix_min'], 0, ',', ' ') : '' }}" class="input">
                        <input name="prix_max" inputmode="numeric" placeholder="Max" aria-label="Prix maximum" value="{{ isset($filtres['prix_max']) ? number_format($filtres['prix_max'], 0, ',', ' ') : '' }}" class="input">
                    </div>
                </div>
                <div>
                    <span class="label">Chambres</span>
                    <div class="grid grid-cols-5 gap-1 text-sm">
                        @foreach (['' => 'Tout', 1 => '1+', 2 => '2+', 3 => '3+', 4 => '4+'] as $v => $l)
                            <label class="cursor-pointer">
                                <input type="radio" name="chambres" value="{{ $v }}" class="peer sr-only" @checked(($filtres['chambres'] ?? '') == $v)>
                                <span class="block rounded-md border border-line py-1.5 text-center peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white">{{ $l }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @if ($transaction !== 'luxe')
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="luxe" value="1" class="rounded border-line text-ink focus:ring-roof" @checked(isset($filtres['luxe']))>
                        Biens de prestige uniquement
                    </label>
                @endif
                @isset($filtres['type'])<input type="hidden" name="type" value="{{ $filtres['type'] }}">@endisset
                <div>
                    <label class="label" for="f-tri">Trier par</label>
                    <select id="f-tri" name="tri" class="input">
                        @foreach (\App\Http\Controllers\Immobilier\Support\RechercheBiens::TRIS as $v => $l)
                            <option value="{{ $v }}" @selected(($filtres['tri'] ?? 'recent') === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 pt-1">
                    <button class="btn-ink flex-1">Appliquer</button>
                    @if ($avecFiltres)<a href="{{ route($route) }}" class="btn-ghost">Effacer</a>@endif
                </div>
                @if ($transaction !== 'luxe')
                    <p class="border-t border-line pt-4 text-sm text-muted">
                        @if ($transaction === 'location')
                            Vous cherchez plutôt à acheter ? <a href="{{ route('immo.achats', collect($filtres)->only(['categorie', 'commune'])->all()) }}" class="link text-ink">Biens à vendre</a>
                        @else
                            Vous cherchez plutôt à louer ? <a href="{{ route('immo.locations', collect($filtres)->only(['categorie', 'commune'])->all()) }}" class="link text-ink">Biens à louer</a>
                        @endif
                    </p>
                @endif
            </form>
        </aside>

        {{-- Résultats --}}
        <div>
            @if (count($marqueurs))
                <div class="mb-5 flex justify-end">
                    <button type="button" @click="carte = !carte" class="btn-line btn-sm">
                        <x-icon name="map-pin" class="size-3.5" /> <span x-text="carte ? 'Masquer la carte' : 'Voir sur la carte'">Voir sur la carte</span>
                    </button>
                </div>
                <template x-if="carte">
                    <div class="mb-6 h-[360px] overflow-hidden rounded-xl border border-line" x-data="pratikMap({ markers: @js($marqueurs) })"></div>
                </template>
            @endif

            @if ($biens->isEmpty())
                <x-empty icon="home" title="Aucune annonce ne correspond à ces critères">
                    Essayez d'élargir le budget ou de choisir une commune voisine.
                    @if ($avecFiltres)<a href="{{ route($route) }}" class="link mt-3 inline-block text-ink">Voir toutes les annonces</a>@endif
                </x-empty>
            @else
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($biens as $bien)
                        <x-bien-card :bien="$bien" />
                    @endforeach
                </div>
                {{ $biens->links() }}
            @endif
        </div>
    </div>
@endsection
