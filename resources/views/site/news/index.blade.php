@extends('layouts.site')

@section('title', $estAujourdhui ? 'Revue de presse : les unes des journaux ivoiriens du jour' : 'Unes des journaux du '.$dateCarbon->isoFormat('D MMMM YYYY'))
@section('description', 'Les unes des quotidiens, hebdomadaires et mensuels ivoiriens du '.$dateCarbon->isoFormat('dddd D MMMM YYYY').' : Fraternité Matin, Le Patriote, Notre Voie, Le Nouveau Réveil…')
@if (! $estAujourdhui && $date !== $derniere)
    @section('canonical', route('news.archives', ['date_archive' => $date]))
@else
    @section('canonical', route('news.index'))
@endif

@section('content')
    <section class="border-b border-line bg-white">
        <div class="container-page pt-8">
            <p class="eyebrow">Revue de presse</p>
            <div class="mt-1 flex flex-col gap-4 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <h1 class="text-3xl font-semibold sm:text-4xl first-letter:uppercase">{{ $dateCarbon->isoFormat('dddd D MMMM YYYY') }}</h1>
                <form method="GET" action="{{ route('news.archives') }}" class="flex items-end gap-2">
                    <div>
                        <label for="date_archive" class="label text-xs">Une autre date</label>
                        <input id="date_archive" type="date" name="date_archive" value="{{ $date }}" max="{{ now()->toDateString() }}" class="input py-2">
                    </div>
                    <button class="btn-line py-2.5">Afficher</button>
                </form>
            </div>
            @include('site.news._onglets')
        </div>
    </section>

    <div class="container-page grid gap-10 py-10 xl:grid-cols-[1fr_320px]">
        <div class="space-y-12">
            @if (! $estAujourdhui && $date === $derniere)
                <p class="rounded-lg bg-amber-wash px-4 py-3 text-sm text-amber-deep">Les unes d'aujourd'hui ne sont pas encore en ligne : voici la dernière parution.</p>
            @endif

            @forelse ($groupes as $groupe)
                <section>
                    <h2 class="flex items-baseline gap-3 text-xl font-semibold">{{ $groupe['libelle'] }} <span class="font-sans text-sm font-normal text-muted">{{ $groupe['unes']->count() }}</span></h2>
                    <ul class="mt-4 grid grid-cols-2 gap-x-4 gap-y-6 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5">
                        @foreach ($groupe['unes'] as $une)
                            <li>
                                <a href="{{ $une->entreprise?->LIEN ? route('news.une', $une->entreprise->LIEN) : '#' }}" class="group block">
                                    <div class="aspect-[3/4] overflow-hidden rounded-md border border-line bg-sand shadow-sm">
                                        <img src="{{ $une->image_url }}" alt="Une de {{ $une->source?->DESIGNATION ?? $une->titre }} du {{ $dateCarbon->isoFormat('D MMMM YYYY') }}" loading="lazy" class="size-full object-cover object-top transition group-hover:scale-[1.02]">
                                    </div>
                                    <p class="mt-2 truncate text-sm font-medium group-hover:text-amber-deep">{{ $une->source?->DESIGNATION ?? \Illuminate\Support\Str::beforeLast($une->titre, ' ') }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <x-empty icon="newspaper" title="Aucune une pour cette date">Choisissez une autre date avec le calendrier ci-dessus.</x-empty>
            @endforelse
        </div>

        @if ($newsWeb->isNotEmpty())
            <aside>
                <h2 class="text-lg font-semibold">Actualité du web</h2>
                <ul class="mt-4 divide-y divide-line">
                    @foreach ($newsWeb as $article)
                        <li class="py-3">
                            <a href="{{ (trim($article->lien ?? '', '/') !== '' ? route('news.article', trim($article->lien, '/')) : ($article->LIEN_WEB ?: route('news.web'))) }}" class="group flex gap-3">
                                <img src="{{ $article->image_url }}" alt="" loading="lazy" class="size-16 shrink-0 rounded-md bg-sand object-cover">
                                <span class="min-w-0">
                                    <span class="line-clamp-3 text-sm font-medium leading-snug group-hover:text-amber-deep">{{ $article->titre }}</span>
                                    <span class="mt-1 block text-xs text-muted">{{ $article->sourceWeb?->DESIGNATION }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('news.web') }}" class="link mt-3 inline-block text-sm">Toute l'actualité du web</a>
            </aside>
        @endif
    </div>
@endsection
