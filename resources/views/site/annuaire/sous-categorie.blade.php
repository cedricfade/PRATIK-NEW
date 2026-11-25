@extends('layouts.site')

@section('title', $sousCategorie->DESIGNATION.($commune ? ' à '.$commune->DESIGNATION : ' en Côte d\'Ivoire'))
@section('description', $sousCategorie->DESCRIPTIF ?: 'Liste des '.mb_strtolower($sousCategorie->DESIGNATION).($commune ? ' à '.$commune->DESIGNATION : ' en Côte d\'Ivoire').' : adresses, téléphones et plans d\'accès sur Pratik.')
@section('canonical', $entreprises->currentPage() > 1 ? $entreprises->url($entreprises->currentPage()) : route('annuaire.sous-categorie', [$categorie->LIEN, $sousCategorie->LIEN]).($commune ? '?commune='.urlencode($commune->LIEN) : ''))

@section('content')
    <x-page-header :title="$sousCategorie->DESIGNATION.($commune ? ' à '.$commune->DESIGNATION : '')" :eyebrow="$categorie->DESIGNATION"
        :breadcrumbs="['Accueil' => route('home'), 'Annuaire' => route('annuaire.index'), $categorie->DESIGNATION => route('annuaire.categorie', $categorie->LIEN), $sousCategorie->DESIGNATION => null]">
        {{ number_format($entreprises->total(), 0, ',', ' ') }} {{ $entreprises->total() > 1 ? 'entreprises' : 'entreprise' }}{{ $commune ? ' à '.$commune->DESIGNATION : '' }}.
    </x-page-header>

    <div class="container-page py-8">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="commune" class="label">Commune</label>
                <select id="commune" name="commune" class="input w-60" onchange="this.form.submit()">
                    <option value="">Toutes les communes</option>
                    @foreach ($communes as $c)
                        <option value="{{ $c->LIEN }}" @selected(($commune->LIEN ?? null) === $c->LIEN)>{{ $c->DESIGNATION }}</option>
                    @endforeach
                </select>
            </div>
            <noscript><button class="btn-line">Filtrer</button></noscript>
            @if ($commune)
                <a href="{{ route('annuaire.sous-categorie', [$categorie->LIEN, $sousCategorie->LIEN]) }}" class="btn-ghost"><x-icon name="x" class="size-4" /> Retirer le filtre</a>
            @endif
        </form>

        @if ($sousCategorie->pub_url)
            <div class="mt-6 overflow-hidden rounded-xl border border-line bg-white">
                @if ($sousCategorie->LIEN_PUB)
                    <a href="{{ $sousCategorie->LIEN_PUB }}" target="_blank" rel="noopener sponsored"><img src="{{ $sousCategorie->pub_url }}" alt="Annonce" class="mx-auto max-h-56 w-auto" loading="lazy"></a>
                @else
                    <img src="{{ $sousCategorie->pub_url }}" alt="Annonce" class="mx-auto max-h-56 w-auto" loading="lazy">
                @endif
            </div>
        @endif

        @if ($top12->isNotEmpty() && $entreprises->onFirstPage())
            <section class="mt-8 rounded-2xl border border-amber/40 bg-amber-wash/40 p-4 sm:p-6">
                <div class="flex items-center gap-2">
                    <span class="grid size-9 place-items-center rounded-full bg-amber text-ink"><x-icon name="crown" class="size-5" /></span>
                    <div>
                        <h2 class="text-xl font-semibold">Top 12 · {{ $sousCategorie->DESIGNATION }}</h2>
                        <p class="text-sm text-muted">Les professionnels partenaires de Pratik dans cette rubrique{{ $commune ? ' à '.$commune->DESIGNATION : '' }}.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @foreach ($top12 as $entreprise)
                        <x-entreprise-card :entreprise="$entreprise" class="border-amber/50" />
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-8">
            <h2 @class(['sr-only' => $top12->isEmpty() || ! $entreprises->onFirstPage(), 'mb-4 text-xl font-semibold'])>Toutes les entreprises</h2>
            @if ($entreprises->isEmpty())
                <x-empty icon="building" title="Aucune entreprise ici pour le moment">
                    @if ($commune) Essayez sans le filtre de commune, ou @endif
                    <a href="{{ route('souscription') }}" class="link text-ink">inscrivez la vôtre</a>.
                </x-empty>
            @else
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach ($entreprises as $entreprise)
                        <x-entreprise-card :entreprise="$entreprise" @class(['border-amber/50 bg-amber-wash/30' => $entreprise->annuaire?->est_client]) />
                    @endforeach
                </div>
                {{ $entreprises->links() }}
            @endif
        </section>
    </div>
@endsection
