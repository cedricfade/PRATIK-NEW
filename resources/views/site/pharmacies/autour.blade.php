@extends('layouts.site')

@section('title', 'Pharmacies de garde autour de vous')
@section('noindex', true)

@section('content')
    <x-page-header title="Pharmacies de garde autour de vous" eyebrow="Pharmacies de garde"
        :breadcrumbs="['Accueil' => route('home'), 'Pharmacies de garde' => route('pharmacies.index'), 'Autour de moi' => null]">
        @if ($pharmacies->isNotEmpty())
            {{ $pharmacies->count() }} {{ $pharmacies->count() > 1 ? 'pharmacies' : 'pharmacie' }} de garde à moins de {{ $rayon }} km, de la plus proche à la plus éloignée.
        @endif
    </x-page-header>

    <div class="container-page grid gap-8 py-10 lg:grid-cols-[1fr_440px]">
        <section>
            @if ($pharmacies->isEmpty())
                <x-empty icon="locate" title="Aucune pharmacie de garde à moins de {{ $rayon }} km">
                    Consultez <a href="{{ route('pharmacies.index') }}" class="link text-ink">la liste par commune</a>.
                </x-empty>
            @else
                <ul class="space-y-3">
                    @foreach ($pharmacies as $pharmacie)
                        <x-site.pharmacie-item :pharmacie="$pharmacie" :distance="(float) $pharmacie->distance" />
                    @endforeach
                </ul>
            @endif
        </section>
        <aside class="lg:sticky lg:top-28 lg:self-start">
            <div class="h-[28rem] overflow-hidden rounded-xl border border-line" x-data="pratikMap({ markers: @js($marqueurs), user: [{{ $lat }}, {{ $lng }}] })"></div>
        </aside>
    </div>
@endsection
