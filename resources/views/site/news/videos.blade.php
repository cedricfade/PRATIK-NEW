@extends('layouts.site')

@section('title', 'Vidéos d\'actualité')
@section('description', 'Reportages et vidéos d\'actualité sur la Côte d\'Ivoire, sélectionnés par Pratik.')

@section('content')
    <section class="border-b border-line bg-white">
        <div class="container-page pt-8">
            <p class="eyebrow">News</p>
            <h1 class="mt-1 pb-6 text-3xl font-semibold sm:text-4xl">Vidéos</h1>
            @include('site.news._onglets')
        </div>
    </section>

    <div class="container-page py-8">
        @if ($videos->isEmpty())
            <x-empty icon="play" title="Aucune vidéo pour le moment" />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($videos as $video)
                    <figure class="card overflow-hidden" x-data="{ lecture: false }">
                        <div class="relative aspect-video bg-ink">
                            <template x-if="lecture">
                                <iframe src="{{ $video->embed_url }}?autoplay=1" title="{{ $video->libelle }}" class="absolute inset-0 size-full" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                            </template>
                            <button type="button" x-show="!lecture" @click="lecture = true" class="group absolute inset-0" aria-label="Lire « {{ $video->libelle }} »">
                                @if ($video->vignette)<img src="{{ $video->vignette }}" alt="" loading="lazy" class="size-full object-cover opacity-90 transition group-hover:opacity-100">@endif
                                <span class="absolute inset-0 m-auto grid size-14 place-items-center rounded-full bg-white/90 text-ink shadow-lg transition group-hover:scale-105"><x-icon name="play" class="ml-1 size-6" /></span>
                            </button>
                        </div>
                        <figcaption class="p-4 text-sm font-medium leading-snug">{{ $video->libelle }}
                            <span class="mt-1 block text-xs font-normal text-muted">{{ $video->created_at?->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
            {{ $videos->links() }}
        @endif
    </div>
@endsection
