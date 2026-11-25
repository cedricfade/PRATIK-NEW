@extends('layouts.immo')

@php
    $nom = \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower($entreprise->TITRE));
    $telephones = $annuaire?->telephones ?? [];
    $suivi = fn (string $type) => Route::has('stats.'.$type) ? "pratikTrack('".route('stats.'.$type, $entreprise->ID)."')" : '';
    $presentation = trim(strip_tags((string) ($annuaire?->DESCRIPTION ?: $annuaire?->RESUME)));
@endphp

@section('title', $nom.' — annonces immobilières')
@section('description', $nom.' : '.($compteurs['ventes'] + $compteurs['locations']).' annonces immobilières en ligne sur Pratik'.($entreprise->commune ? ', '.$entreprise->commune->DESIGNATION : '').'. '.\Illuminate\Support\Str::limit($presentation, 100))
@section('canonical', route('immo.annonceur', $entreprise->LIEN))
@if ($logo)
    @section('og_image', $logo)
@endif

@section('content')
    <section class="border-b border-line bg-white">
        <div class="container-page py-8">
            <nav class="flex items-center gap-1 text-[13px] text-muted" aria-label="Fil d'Ariane">
                <a href="{{ route('immo.home') }}" class="hover:text-ink">Immobilier</a>
                <x-icon name="chevron-right" class="size-3.5" />
                <a href="{{ route('immo.agences') }}" class="hover:text-ink">Professionnels</a>
            </nav>
            <div class="mt-5 flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-5">
                    <x-logo-tile :src="$logo" :name="$entreprise->TITRE" size="size-24" />
                    <div>
                        <h1 class="text-3xl font-semibold">{{ $nom }}</h1>
                        <p class="mt-1 text-ink/70">
                            @if ($entreprise->commune){{ $entreprise->commune->DESIGNATION }} · @endif
                            {{ $compteurs['ventes'] }} à vendre · {{ $compteurs['locations'] }} à louer
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2 text-sm">
                            <span class="tag py-1"><x-icon name="home" class="size-4" /> {{ $compteurs['ventes'] + $compteurs['locations'] }} {{ $compteurs['ventes'] + $compteurs['locations'] > 1 ? 'biens en ligne' : 'bien en ligne' }}</span>
                            <x-vues :nombre="$entreprise->vues_affichees" class="tag py-1" />
                        </div>
                        @if ($presentation)
                            <p class="mt-3 max-w-2xl text-sm leading-6 text-ink/75" x-data="{ plus: false }">
                                <span :class="plus ? '' : 'line-clamp-3'">{{ $presentation }}</span>
                                @if (mb_strlen($presentation) > 250)<button type="button" @click="plus = !plus" class="link mt-1 text-ink" x-text="plus ? 'Réduire' : 'Lire la suite'">Lire la suite</button>@endif
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 lg:max-w-xs lg:justify-end">
                    @foreach (array_slice($telephones, 0, 2) as $tel)
                        <a href="{{ tel_link($tel) }}" onclick="{{ $suivi('appel') }}" class="{{ $loop->first ? 'btn-ink' : 'btn-line' }}"><x-icon name="phone" class="size-4" /> {{ $tel }}</a>
                    @endforeach
                    @if ($email = ($annuaire?->emails[0] ?? null))
                        <a href="mailto:{{ $email }}" onclick="{{ $suivi('email') }}" class="btn-line"><x-icon name="mail" class="size-4" /> Écrire</a>
                    @endif
                    @if ($annuaire?->site_url)
                        <a href="{{ $annuaire->site_url }}" target="_blank" rel="noopener nofollow" class="btn-line"><x-icon name="globe" class="size-4" /> Site web</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="container-page py-8" x-data="{ carte: false }">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="inline-flex rounded-lg bg-sand p-1 text-sm font-medium">
                @foreach ([null => 'Toutes', 'vente' => 'À vendre', 'location' => 'À louer'] as $t => $label)
                    <a href="{{ route('immo.annonceur', array_filter(['lien' => $entreprise->LIEN, 'transaction' => $t])) }}" @class(['rounded-md px-3.5 py-1.5', 'bg-white text-ink shadow-sm' => $transaction === ($t ?: null), 'text-ink/60 hover:text-ink' => $transaction !== ($t ?: null)])>{{ $label }}</a>
                @endforeach
            </div>
            @if (count($marqueurs))
                <button type="button" @click="carte = !carte" class="btn-line btn-sm"><x-icon name="map-pin" class="size-3.5" /> <span x-text="carte ? 'Masquer la carte' : 'Voir sur la carte'">Voir sur la carte</span></button>
            @endif
        </div>

        @if (count($marqueurs))
            <template x-if="carte">
                <div class="mt-5 h-[360px] overflow-hidden rounded-xl border border-line" x-data="pratikMap({ markers: @js($marqueurs) })"></div>
            </template>
        @endif

        @if ($biens->isEmpty())
            <x-empty icon="home" title="Aucune annonce en ligne" class="mt-6">Cet annonceur n'a pas d'annonce correspondante pour le moment.</x-empty>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($biens as $bien)
                    <x-bien-card :bien="$bien" />
                @endforeach
            </div>
            {{ $biens->links() }}
        @endif
    </div>
@endsection
