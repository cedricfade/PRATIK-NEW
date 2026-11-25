@extends('layouts.site')

@section('title', 'Papiers administratifs'.($commune ? ' à '.$commune->DESIGNATION : ' en Côte d\'Ivoire'))
@section('description', 'Passeport, carte d\'identité, casier judiciaire, actes d\'état civil : les pièces à fournir et les sites officiels pour faire vos démarches'.($commune ? ' à '.$commune->DESIGNATION : ' en Côte d\'Ivoire').'.')

@section('content')
    <x-page-header :title="'Papiers administratifs'.($commune ? ' à '.$commune->DESIGNATION : '')" eyebrow="Démarches"
        :breadcrumbs="['Accueil' => route('home'), 'Papiers administratifs' => $commune ? route('papiers.index') : null] + ($commune ? [$commune->DESIGNATION => null] : [])">
        Les pièces à réunir et le bon site officiel pour chaque démarche. Vérifiez toujours les conditions sur le site de l'administration avant de vous déplacer.
        @if ($communes->isNotEmpty())
            <x-slot:actions>
                <form method="GET" x-data @submit.prevent="window.location = $refs.ville.value" class="flex flex-wrap items-end gap-2">
                    <div>
                        <label for="ville" class="label">Démarches propres à une ville</label>
                        <select id="ville" x-ref="ville" class="input w-64" @change="window.location = $event.target.value">
                            <option value="{{ route('papiers.index') }}">Choisir une ville ou commune</option>
                            @foreach ($communes as $ville)
                                <option value="{{ route('papiers.index', $ville->LIEN) }}" @selected($commune && $commune->ID === $ville->ID)>{{ $ville->DESIGNATION }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </x-slot:actions>
        @endif
    </x-page-header>

    <div class="container-page space-y-12 py-10">
        @if ($commune)
            <section>
                <h2 class="section-title">Mairie et services à {{ $commune->DESIGNATION }}</h2>
                @if ($itemsCommune->isEmpty())
                    <x-empty icon="file-text" title="Aucun lien pour {{ $commune->DESIGNATION }} pour le moment" class="mt-4" />
                @else
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @foreach ($itemsCommune as $item)
                            @include('site._papier', ['item' => $item])
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        <section>
            @if ($commune)<h2 class="section-title">Démarches nationales</h2>@endif
            @if ($items->isEmpty())
                <x-empty icon="file-text" title="Aucune démarche publiée pour le moment" class="mt-4" />
            @else
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach ($items as $item)
                        @include('site._papier', ['item' => $item])
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
