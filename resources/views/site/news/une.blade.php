@extends('layouts.site')

@php
    $journal = $une->source?->DESIGNATION ?? \Illuminate\Support\Str::beforeLast($une->titre, ' ');
    $jour = \Carbon\Carbon::parse($une->DATE_PARUTION)->locale('fr');
@endphp

@section('title', 'Une de '.$journal.' du '.$jour->isoFormat('D MMMM YYYY'))
@section('description', 'La une de '.$journal.' ('.mb_strtolower($une->typeNews?->DESIGNATION ?? 'journal').') du '.$jour->isoFormat('dddd D MMMM YYYY').', dans la revue de presse Pratik.')
@section('og_image', $une->image_url ?? '')
@section('og_type', 'article')

@section('content')
    <x-page-header :title="$journal" :eyebrow="($une->typeNews?->DESIGNATION ?? 'Presse').' · '.$jour->isoFormat('dddd D MMMM YYYY')"
        :breadcrumbs="['Accueil' => route('home'), 'News' => route('news.index'), $jour->isoFormat('D MMMM YYYY') => route('news.archives', ['date_archive' => $jour->toDateString()]), $journal => null]" />

    <div class="container-page grid gap-10 py-10 lg:grid-cols-[minmax(0,640px)_1fr]">
        <figure>
            <a href="{{ $une->image_url }}" target="_blank" rel="noopener" class="block overflow-hidden rounded-lg border border-line bg-white shadow-[var(--shadow-lift)]">
                <img src="{{ $une->image_url }}" alt="Une de {{ $journal }} du {{ $jour->isoFormat('D MMMM YYYY') }}" class="w-full">
            </a>
            <figcaption class="mt-3 text-sm text-muted">Cliquez sur l'image pour l'ouvrir en grand.</figcaption>
            <div class="mt-6 flex justify-between gap-3">
                @if ($precedente?->entreprise?->LIEN)
                    <a href="{{ route('news.une', $precedente->entreprise->LIEN) }}" class="btn-line"><x-icon name="arrow-left" class="size-4" /> {{ $precedente->source?->DESIGNATION ?? 'Précédente' }}</a>
                @else <span></span> @endif
                @if ($suivante?->entreprise?->LIEN)
                    <a href="{{ route('news.une', $suivante->entreprise->LIEN) }}" class="btn-line">{{ $suivante->source?->DESIGNATION ?? 'Suivante' }} <x-icon name="arrow-right" class="size-4" /></a>
                @endif
            </div>
        </figure>

        <aside>
            <h2 class="text-lg font-semibold">Les autres unes du jour</h2>
            <ul class="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-3">
                @foreach ($memeJour as $autre)
                    @continue($autre->ID === $une->ID || ! $autre->entreprise?->LIEN)
                    <li>
                        <a href="{{ route('news.une', $autre->entreprise->LIEN) }}" class="group block">
                            <div class="aspect-[3/4] overflow-hidden rounded border border-line bg-sand"><img src="{{ $autre->image_url }}" alt="{{ $autre->source?->DESIGNATION }}" loading="lazy" class="size-full object-cover object-top"></div>
                            <p class="mt-1 truncate text-xs group-hover:text-amber-deep">{{ $autre->source?->DESIGNATION }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
@endsection
