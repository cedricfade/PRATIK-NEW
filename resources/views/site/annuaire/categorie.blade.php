@extends('layouts.site')

@section('title', $categorie->DESIGNATION.' — Annuaire')
@section('description', $categorie->DESCRIPTION ?: 'Annuaire '.$categorie->DESIGNATION.' en Côte d\'Ivoire : '.$sousCategories->pluck('DESIGNATION')->take(8)->implode(', ').'.')

@section('content')
    <x-page-header :title="$categorie->DESIGNATION" eyebrow="Annuaire" :breadcrumbs="['Accueil' => route('home'), 'Annuaire' => route('annuaire.index'), $categorie->DESIGNATION => null]">
        {{ $sousCategories->count() }} rubriques, {{ number_format($sousCategories->sum('total'), 0, ',', ' ') }} fiches au total.
    </x-page-header>

    <div class="container-page grid gap-10 py-10 lg:grid-cols-[1fr_340px]">
        <section>
            <h2 class="sr-only">Rubriques</h2>
            @if ($sousCategories->isEmpty())
                <x-empty icon="layers" title="Aucune rubrique active">Revenez bientôt, cette catégorie est en cours d'organisation.</x-empty>
            @else
                <ul class="grid gap-x-8 sm:grid-cols-2">
                    @foreach ($sousCategories as $sous)
                        <li class="border-b border-line">
                            <a href="{{ route('annuaire.sous-categorie', [$categorie->LIEN, $sous->LIEN]) }}" class="group flex items-center justify-between gap-4 py-3">
                                <span class="font-medium text-ink group-hover:text-amber-deep">{{ $sous->DESIGNATION }}</span>
                                <span class="shrink-0 text-sm text-muted">{{ number_format($sous->total, 0, ',', ' ') }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="space-y-4">
            @if ($clients->isNotEmpty())
                <h2 class="flex items-center gap-2 text-lg font-semibold"><x-icon name="crown" class="size-5 text-amber-deep" /> Les pros TOP 12</h2>
                @foreach ($clients as $entreprise)
                    <x-entreprise-card :entreprise="$entreprise" :show-phone="false" />
                @endforeach
            @endif
            <div class="rounded-xl bg-ink p-5 text-white/80">
                <p class="font-serif text-lg text-white">Vous exercez dans ce secteur ?</p>
                <p class="mt-2 text-sm">Apparaissez en tête de votre rubrique avec votre logo.</p>
                <a href="{{ route('souscription') }}" class="btn-amber mt-4">Devenir partenaire</a>
            </div>
        </aside>
    </div>
@endsection
