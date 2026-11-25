@extends('layouts.site')

@php($jour = \Carbon\Carbon::parse($article->DATE_PARUTION)->locale('fr'))

@section('title', $article->titre)
@section('description', \App\Services\HtmlPropre::texte($article->RESUME ?: $article->titre, 158))
@section('og_image', $article->image_url ?? '')
@section('og_type', 'article')

@section('content')
    <article class="container-page grid gap-10 py-10 lg:grid-cols-[minmax(0,720px)_1fr]">
        <div>
            <nav class="mb-4 flex flex-wrap items-center gap-1 text-[13px] text-muted">
                <a href="{{ route('home') }}" class="hover:text-ink">Accueil</a><x-icon name="chevron-right" class="size-3.5" />
                <a href="{{ route('news.web') }}" class="hover:text-ink">Actualité du web</a>
            </nav>
            <p class="eyebrow">{{ $article->sourceWeb?->DESIGNATION ?? 'Actualité' }} · {{ $jour->isoFormat('D MMMM YYYY') }}</p>
            <h1 class="mt-2 text-3xl font-semibold leading-tight sm:text-4xl">{{ $article->titre }}</h1>
            @if ($article->image_url)
                <img src="{{ $article->image_url }}" alt="" class="mt-6 w-full rounded-lg bg-sand object-cover">
            @endif
            @if ($article->RESUME || $article->TEXTE)
                <div class="prose-pratik mt-6 text-[17px]">{!! \App\Services\HtmlPropre::nettoyer($article->RESUME ?: $article->TEXTE) !!}</div>
            @endif
            @if ($article->LIEN_WEB)
                <a href="{{ $article->LIEN_WEB }}" target="_blank" rel="noopener nofollow" class="btn-ink mt-6">Lire l'article complet sur {{ $article->sourceWeb?->DESIGNATION ?? 'le site source' }} <x-icon name="external" class="size-4" /></a>
            @endif
        </div>
        @if ($autres->isNotEmpty())
            <aside>
                <h2 class="text-lg font-semibold">À lire aussi</h2>
                <ul class="mt-4 divide-y divide-line">
                    @foreach ($autres as $a)
                        <li class="py-3"><a href="{{ (trim($a->lien ?? '', '/') !== '' ? route('news.article', trim($a->lien, '/')) : ($a->LIEN_WEB ?: route('news.web'))) }}" class="group flex gap-3">
                            <img src="{{ $a->image_url }}" alt="" loading="lazy" class="size-16 shrink-0 rounded-md bg-sand object-cover">
                            <span class="min-w-0"><span class="line-clamp-3 text-sm font-medium group-hover:text-amber-deep">{{ $a->titre }}</span><span class="mt-1 block text-xs text-muted">{{ $a->sourceWeb?->DESIGNATION }}</span></span>
                        </a></li>
                    @endforeach
                </ul>
            </aside>
        @endif
    </article>
@endsection
