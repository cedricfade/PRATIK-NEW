@extends('layouts.site')
@section('title', 'Page introuvable')
@section('noindex', true)

@section('content')
    <div class="container-page grid gap-10 py-16 lg:grid-cols-2 lg:py-24">
        <div>
            <p class="eyebrow">Erreur 404</p>
            <h1 class="mt-3 text-4xl font-semibold leading-tight">Cette page n'existe pas, ou plus.</h1>
            <p class="mt-4 max-w-md text-ink/75">L'entreprise a peut-être changé de nom, ou l'annonce a été retirée. Une recherche dans l'annuaire devrait vous remettre sur la bonne piste.</p>
            <form action="{{ route('recherche') }}" method="GET" class="mt-6 flex max-w-md gap-2">
                <input name="q" type="search" required minlength="2" placeholder="Nom, sigle, métier…" class="input">
                <button class="btn-ink">Chercher</button>
            </form>
        </div>
        <ul class="grid content-start gap-2 text-sm">
            @foreach (['annuaire.index' => 'Annuaire des entreprises', 'pharmacies.index' => 'Pharmacies de garde', 'news.index' => 'Revue de presse du jour', 'papiers.index' => 'Papiers administratifs', 'immo.home' => 'Pratik Immobilier'] as $route => $label)
                <li><a href="{{ route($route) }}" class="card card-hover flex items-center justify-between px-4 py-3 font-medium">{{ $label }} <x-icon name="arrow-right" class="size-4 text-ink/40" /></a></li>
            @endforeach
        </ul>
    </div>
@endsection
