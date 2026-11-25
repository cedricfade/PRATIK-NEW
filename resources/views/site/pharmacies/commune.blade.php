@extends('layouts.site')

@section('title', 'Pharmacies de garde à '.$commune->DESIGNATION)
@section('description', 'Pharmacies de garde à '.$commune->DESIGNATION.' '.($periodeGarde ? $periodeGarde->libelle : 'cette semaine').' : adresses, téléphones et itinéraires.')
@section('canonical', route('pharmacies.commune', $commune->LIEN))

@section('content')
    <x-page-header :title="'Pharmacies de garde à '.$commune->DESIGNATION" eyebrow="Pharmacies de garde"
        :breadcrumbs="['Accueil' => route('home'), 'Pharmacies de garde' => route('pharmacies.index'), $commune->DESIGNATION => null]">
        @if ($periodeGarde)Semaine {{ $periodeGarde->libelle }} · @endif{{ $pharmacies->total() }} {{ $pharmacies->total() > 1 ? 'pharmacies' : 'pharmacie' }}
    </x-page-header>

    <div class="container-page grid gap-8 py-10 lg:grid-cols-[1fr_420px]">
        <section>
            @if ($pharmacies->isEmpty())
                <x-empty icon="cross" title="Aucune pharmacie de garde enregistrée ici cette semaine">
                    <a href="{{ route('pharmacies.index') }}" class="link text-ink">Voir les autres communes</a> ou utilisez la recherche autour de vous.
                </x-empty>
            @else
                <ul class="space-y-3">
                    @foreach ($pharmacies as $pharmacie)
                        <x-site.pharmacie-item :pharmacie="$pharmacie" />
                    @endforeach
                </ul>
                {{ $pharmacies->links() }}
            @endif
        </section>

        <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
            @if ($marqueurs->isNotEmpty())
                <div class="h-96 overflow-hidden rounded-xl border border-line" x-data="pratikMap({ markers: @js($marqueurs) })"></div>
            @endif
            @include('site.pharmacies._autour')
            @if ($autres->count() > 1)
                <div class="card p-5">
                    <p class="text-sm font-semibold">Autres communes</p>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($autres as $a)
                            @continue($a->LIEN === $commune->LIEN)
                            <a href="{{ route('pharmacies.commune', $a->LIEN) }}" class="tag hover:border-garde/40 hover:text-garde">{{ $a->DESIGNATION }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
@endsection
