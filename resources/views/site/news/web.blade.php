@extends('layouts.site')

@section('title', $source ? 'Actualité : '.$source->DESIGNATION : 'Actualité du web')
@section('description', 'Une sélection quotidienne d\'articles de la presse ivoirienne et internationale'.($source ? ' — '.$source->DESIGNATION : '').'.')

@section('content')
    <section class="border-b border-line bg-white">
        <div class="container-page pt-8">
            <p class="eyebrow">News</p>
            <h1 class="mt-1 pb-6 text-3xl font-semibold sm:text-4xl">Actualité du web{{ $source ? ' · '.$source->DESIGNATION : '' }}</h1>
            @include('site.news._onglets')
        </div>
    </section>

    <div class="container-page py-8">
        @if ($sources->isNotEmpty())
            <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-2 sm:mx-0 sm:flex-wrap sm:px-0">
                <a href="{{ route('news.web') }}" @class(['tag whitespace-nowrap', '!border-ink !bg-ink !text-white' => ! $source])>Toutes les sources</a>
                @foreach ($sources as $s)
                    <a href="{{ route('news.web', ['source' => $s->ID]) }}" @class(['tag whitespace-nowrap', '!border-ink !bg-ink !text-white' => $source?->ID === $s->ID])>{{ $s->DESIGNATION }}</a>
                @endforeach
            </div>
        @endif

        @if ($articles->isEmpty())
            <x-empty icon="globe" title="Aucun article" class="mt-6" />
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <article class="card card-hover group relative flex flex-col overflow-hidden">
                        <div class="aspect-[16/9] bg-sand"><img src="{{ $article->image_url }}" alt="" loading="lazy" class="size-full object-cover"></div>
                        <div class="flex flex-1 flex-col p-4">
                            <p class="flex items-center gap-2 text-xs text-muted">
                                @if ($article->sourceWeb?->logo_url)<img src="{{ $article->sourceWeb->logo_url }}" alt="" class="size-4 rounded-sm object-contain">@endif
                                {{ $article->sourceWeb?->DESIGNATION }} · {{ \Carbon\Carbon::parse($article->DATE_PARUTION)->locale('fr')->isoFormat('D MMM YYYY') }}
                            </p>
                            <h2 class="mt-2 font-sans text-[15px] font-semibold leading-snug">
                                <a href="{{ (trim($article->lien ?? '', '/') !== '' ? route('news.article', trim($article->lien, '/')) : ($article->LIEN_WEB ?: route('news.web'))) }}" class="after:absolute after:inset-0 group-hover:text-amber-deep">{{ $article->titre }}</a>
                            </h2>
                            @if ($article->RESUME)<p class="mt-2 line-clamp-3 text-sm text-ink/70">{{ \App\Services\HtmlPropre::texte($article->RESUME, 220) }}</p>@endif
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $articles->links() }}
        @endif
    </div>
@endsection
