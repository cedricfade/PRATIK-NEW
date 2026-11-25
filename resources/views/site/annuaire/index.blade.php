@extends('layouts.site')

@section('title', 'Annuaire des entreprises de Côte d\'Ivoire')
@section('description', 'Trouvez les coordonnées, l\'adresse et le plan d\'accès de '.number_format($categories->sum('total'), 0, ',', ' ').' entreprises ivoiriennes, classées par secteur d\'activité.')

@php
    $icones = [
        'activites-juridiques' => 'briefcase', 'commerces' => 'tag', 'communication-publicite' => 'globe',
        'industries' => 'building', 'informatique' => 'smartphone', 'services' => 'users', 'administration' => 'file-text',
        'agriculture' => 'trees', 'batiment-construction' => 'home', 'education-formation' => 'newspaper',
        'transport-logistique' => 'truck', 'telecommunication' => 'phone', 'loisirs' => 'star', 'mode-beaute' => 'crown',
        'sante' => 'cross', 'securite' => 'shield', 'sorties' => 'calendar', 'finances-assurances' => 'banknote',
        'auto-moto' => 'navigation', 'immobilier' => 'home',
    ];
@endphp

@section('content')
    <x-page-header title="Annuaire des entreprises" eyebrow="Côte d'Ivoire" :breadcrumbs="['Accueil' => route('home'), 'Annuaire' => null]">
        {{ number_format($categories->sum('total'), 0, ',', ' ') }} entreprises classées par secteur. Cherchez par nom, par sigle ou par métier, puis affinez par commune.
        <x-slot:actions>
            <div class="max-w-3xl">@include('site.annuaire._recherche')</div>
        </x-slot:actions>
    </x-page-header>

    <section class="container-page py-12">
        <h2 class="section-title">Parcourir par secteur</h2>
        <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($categories as $categorie)
                <li>
                    <a href="{{ route('annuaire.categorie', $categorie->LIEN) }}" class="card card-hover group flex items-center gap-3 p-4">
                        <span class="grid size-10 shrink-0 place-items-center rounded-lg {{ $categorie->LIEN === 'sante' ? 'bg-garde-wash text-garde' : 'bg-sand text-ink/80' }}">
                            <x-icon :name="$icones[$categorie->LIEN] ?? 'layers'" class="size-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium text-ink">{{ $categorie->DESIGNATION }}</span>
                            <span class="text-[13px] text-muted">{{ number_format($categorie->total, 0, ',', ' ') }} entreprises</span>
                        </span>
                        <x-icon name="chevron-right" class="size-4 text-ink/30 transition group-hover:translate-x-0.5 group-hover:text-ink" />
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-14 grid gap-6 rounded-xl border border-line bg-white p-6 sm:p-8 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <h2 class="text-xl font-semibold">Votre entreprise n'apparaît pas, ou ses informations ont changé ?</h2>
                <p class="mt-2 text-ink/75">L'inscription de base est gratuite. Une fiche à jour, avec logo et plan d'accès, vous fait gagner des clients.</p>
            </div>
            <a href="{{ route('souscription') }}" class="btn-ink">Inscrire ou mettre à jour ma fiche</a>
        </div>
    </section>
@endsection
