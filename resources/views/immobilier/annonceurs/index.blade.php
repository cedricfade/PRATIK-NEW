@extends('layouts.immo')

@section('title', $page['titre'].' en Côte d\'Ivoire')
@section('description', $page['intro'])

@section('content')
    <x-page-header tone="immo" :title="$page['titre']" :breadcrumbs="['Immobilier' => route('immo.home'), $page['titre'] => null]">
        {{ $page['intro'] }}
        <x-slot:actions>
            <div class="flex flex-wrap gap-2 text-sm">
                @foreach (['agences' => ['Agences', 'immo.agences'], 'promoteurs' => ['Promoteurs', 'immo.promoteurs'], 'amenageurs' => ['Aménageurs fonciers', 'immo.amenageurs']] as $cle => [$label, $route])
                    <a href="{{ route($route) }}" @class(['rounded-full border px-3.5 py-1.5 font-medium', 'border-ink bg-ink text-white' => $categorie === $cle, 'border-line bg-white text-ink/75 hover:border-ink/30' => $categorie !== $cle])>{{ $label }}</a>
                @endforeach
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="container-page py-10">
        @if (empty($annonceurs))
            <x-empty icon="briefcase" title="Aucun annonceur pour le moment">
                Les professionnels apparaissent ici dès que leurs premières annonces sont en ligne.
            </x-empty>
        @else
            <p class="text-sm text-muted">{{ count($annonceurs) }} professionnels avec des annonces en ligne, classés par nombre d'annonces.</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($annonceurs as $annonceur)
                    <x-immo.annonceur-card :annonceur="$annonceur" />
                @endforeach
            </div>
        @endif

        <div class="mt-14 flex flex-col items-start justify-between gap-4 rounded-xl border border-roof/40 bg-roof/10 p-6 sm:flex-row sm:items-center">
            <div>
                <p class="font-semibold">Vous êtes un professionnel de l'immobilier ?</p>
                <p class="text-sm text-ink/70">Publiez vos biens sur Pratik et recevez directement les demandes des acheteurs et locataires.</p>
            </div>
            <a href="{{ route('immo.contact') }}" class="btn-roof shrink-0">Devenir annonceur</a>
        </div>
    </div>
@endsection
