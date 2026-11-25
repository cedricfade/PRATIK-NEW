@extends('layouts.immo')

@section('title', 'Achat, vente et location de biens en Côte d\'Ivoire')
@section('description', number_format($compteurs['ventes'], 0, ',', ' ').' biens à vendre et '.number_format($compteurs['locations'], 0, ',', ' ').' à louer à Abidjan et en Côte d\'Ivoire : appartements, villas, terrains, bureaux. Annonces publiées par des agences et promoteurs, contact direct.')

@section('content')
    {{-- Hero (image de fond et textes réglables dans Admin › Apparence) + recherche + 3 biens au hasard --}}
    <x-hero :hero="$hero" tone="immo">
        <x-immo.recherche :categories="$categories" :communes="$communes" class="text-ink" />

        @if ($aLaUne->isNotEmpty())
            <x-slot:aside>
                <div class="hidden grid-cols-2 gap-3 lg:grid">
                    @foreach ($aLaUne as $i => $bien)
                        <a href="{{ route('immo.bien', $bien->entreprise->LIEN) }}" @class([
                            'group relative overflow-hidden rounded-xl bg-sand shadow-[var(--shadow-lift)] ring-1 ring-white/20',
                            'row-span-2 aspect-[3/4]' => $i === 0,
                            'aspect-[4/3]' => $i > 0,
                        ])>
                            <img src="{{ $bien->image_url }}" alt="{{ $bien->entreprise->TITRE }}" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]" @if ($i > 0) loading="lazy" @endif>
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/85 to-transparent p-4 pt-10 text-white">
                                <p class="font-serif text-lg font-semibold">{{ $bien->prix_affiche }}</p>
                                <p class="line-clamp-1 text-[13px] text-white/80">{{ $bien->categorie?->DESIGNATION }} · {{ $bien->entreprise->commune?->DESIGNATION }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </x-slot:aside>
        @endif
    </x-hero>

    {{-- Dernières ventes --}}
    @if ($ventes->isNotEmpty())
        <section class="container-page py-14">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow !text-roof-deep">À vendre</p>
                    <h2 class="section-title mt-1">Les dernières mises en vente</h2>
                </div>
                <a href="{{ route('immo.achats') }}" class="link hidden text-sm font-medium sm:inline">Tous les biens à vendre</a>
            </div>
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($ventes as $bien)
                    <x-bien-card :bien="$bien" />
                @endforeach
            </div>
            <a href="{{ route('immo.achats') }}" class="btn-line mt-6 w-full sm:hidden">Tous les biens à vendre</a>
        </section>
    @endif

    {{-- Dernières locations --}}
    @if ($locations->isNotEmpty())
        <section class="border-y border-line bg-white py-14">
            <div class="container-page">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow !text-roof-deep">À louer</p>
                        <h2 class="section-title mt-1">Les dernières locations</h2>
                    </div>
                    <a href="{{ route('immo.locations') }}" class="link hidden text-sm font-medium sm:inline">Toutes les locations</a>
                </div>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($locations as $bien)
                        <x-bien-card :bien="$bien" />
                    @endforeach
                </div>
                <a href="{{ route('immo.locations') }}" class="btn-line mt-6 w-full sm:hidden">Toutes les locations</a>
            </div>
        </section>
    @endif

    {{-- Carte --}}
    @if (count($marqueurs))
        <section class="container-page py-14">
            <div class="grid gap-8 lg:grid-cols-[1fr_2fr] lg:items-center">
                <div>
                    <p class="eyebrow !text-roof-deep">Sur la carte</p>
                    <h2 class="section-title mt-1">Repérez le quartier avant la visite</h2>
                    <p class="mt-3 text-ink/75">
                        Les {{ count($marqueurs) }} annonces les plus récentes qui indiquent leur emplacement.
                        Cliquez sur un repère pour voir le prix et ouvrir la fiche.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('immo.achats') }}" class="btn-ink">Biens à vendre</a>
                        <a href="{{ route('immo.locations') }}" class="btn-line">Biens à louer</a>
                    </div>
                </div>
                <div class="h-[420px] overflow-hidden rounded-xl border border-line" x-data="pratikMap({ markers: @js($marqueurs), zoom: 11 })"></div>
            </div>
        </section>
    @endif

    {{-- Annonceurs --}}
    @if ($annonceurs->isNotEmpty())
        <section class="bg-ink py-14 text-white">
            <div class="container-page">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow !text-roof">Ils publient sur Pratik</p>
                        <h2 class="mt-1 font-serif text-2xl font-semibold text-white sm:text-3xl">Agences et promoteurs partenaires</h2>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('immo.agences') }}" class="btn border border-white/20 text-white hover:bg-white/10">Les agences</a>
                        <a href="{{ route('immo.promoteurs') }}" class="btn border border-white/20 text-white hover:bg-white/10">Les promoteurs</a>
                    </div>
                </div>
                <div class="mt-8 grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                    @foreach ($annonceurs as $a)
                        <a href="{{ route('immo.annonceur', $a['lien']) }}" class="group grid aspect-[4/3] place-items-center rounded-lg bg-white p-3" title="{{ $a['titre'] }}">
                            <img src="{{ $a['logo_url'] }}" alt="{{ $a['titre'] }}" loading="lazy" class="max-h-full max-w-full object-contain opacity-90 transition group-hover:opacity-100">
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Financement + annonceurs --}}
    <section class="container-page grid gap-6 py-14 lg:grid-cols-2">
        <div class="card flex flex-col justify-between gap-6 p-7">
            <div>
                <x-icon name="banknote" class="size-7 text-roof-deep" />
                <h2 class="mt-3 text-2xl font-semibold">Combien pouvez-vous emprunter ?</h2>
                <p class="mt-2 text-ink/75">Estimez vos mensualités et votre taux d'endettement avant de visiter, avec notre partenaire bancaire.</p>
            </div>
            <a href="{{ route('immo.financement') }}" class="btn-ink self-start">Simuler mon prêt</a>
        </div>
        <div class="card flex flex-col justify-between gap-6 border-roof/40 bg-roof/10 p-7">
            <div>
                <x-icon name="briefcase" class="size-7 text-roof-deep" />
                <h2 class="mt-3 text-2xl font-semibold">Agence, promoteur, propriétaire ?</h2>
                <p class="mt-2 text-ink/75">Publiez vos biens, recevez les demandes dans votre espace et suivez combien de personnes consultent vos annonces.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('immo.pro.biens.create') }}" class="btn-roof">Déposer une annonce</a>
                <a href="{{ route('immo.contact') }}" class="btn-line">Ouvrir un compte annonceur</a>
            </div>
        </div>
    </section>
@endsection
