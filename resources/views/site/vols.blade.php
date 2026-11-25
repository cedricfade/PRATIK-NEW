@extends('layouts.site')

@section('title', 'Horaires des vols — '.($aeroports[$aeroport] ?? $aeroport))
@section('description', 'Départs et arrivées en temps réel à l\'aéroport '.($aeroports[$aeroport] ?? $aeroport).' : compagnies, numéros de vol, retards et annulations.')

@php
    $statuts = [
        'prevu' => ['Prévu', 'tag'],
        'embarquement' => ['Embarquement', 'tag-amber'],
        'decolle' => ['Décollé', 'tag-garde'],
        'en-vol' => ['En vol', 'tag-garde'],
        'arrive' => ['Arrivé', 'tag-garde'],
        'retarde' => ['Retardé', 'tag-amber'],
        'annule' => ['Annulé', 'tag !border-danger/30 !bg-red-50 !text-danger'],
        'deroute' => ['Dérouté', 'tag !border-danger/30 !bg-red-50 !text-danger'],
    ];
@endphp

@section('content')
    <x-page-header title="Horaires des vols" eyebrow="{{ $aeroports[$aeroport] ?? $aeroport }}" :breadcrumbs="['Accueil' => route('home'), 'Horaires des vols' => null]">
        <span class="first-letter:uppercase">{{ $date->isoFormat('dddd D MMMM YYYY') }}</span> · heures locales de l'aéroport.
        <x-slot:actions>
            <form method="GET" class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="label" for="aeroport">Aéroport</label>
                    <select id="aeroport" name="aeroport" class="input w-72">
                        @foreach ($aeroports as $code => $nom)
                            <option value="{{ $code }}" @selected($code === $aeroport)>{{ $nom }} ({{ $code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="date">Date</label>
                    <input id="date" type="date" name="date" value="{{ $date->toDateString() }}" min="{{ now()->subDay()->toDateString() }}" max="{{ now()->addDays(2)->toDateString() }}" class="input">
                </div>
                <button class="btn-ink py-2.5">Voir les vols</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="container-page py-8" x-data="{ onglet: 'departs', filtre: '' }">
        @if ($erreur)
            <div class="mb-6 flex gap-3 rounded-lg border border-amber/40 bg-amber-wash px-4 py-3 text-sm text-amber-deep">
                <x-icon name="alert" class="mt-0.5 size-4 shrink-0" />
                <p>Les horaires ne sont pas disponibles pour le moment. Réessayez dans quelques minutes, ou consultez le site de votre compagnie.</p>
            </div>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex rounded-lg border border-line bg-white p-1">
                <button type="button" @click="onglet = 'departs'" :class="onglet === 'departs' ? 'bg-ink text-white' : 'text-ink/70'" class="rounded-md px-4 py-2 text-sm font-medium">Départs ({{ count($departs) }})</button>
                <button type="button" @click="onglet = 'arrivees'" :class="onglet === 'arrivees' ? 'bg-ink text-white' : 'text-ink/70'" class="rounded-md px-4 py-2 text-sm font-medium">Arrivées ({{ count($arrivees) }})</button>
            </div>
            <label class="relative sm:w-72">
                <span class="sr-only">Filtrer</span>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted" />
                <input type="search" x-model="filtre" placeholder="Ville, compagnie, n° de vol…" class="input pl-9">
            </label>
        </div>

        @foreach (['departs' => $departs, 'arrivees' => $arrivees] as $sens => $vols)
            <div x-show="onglet === '{{ $sens }}'" @if ($sens === 'arrivees') x-cloak @endif class="mt-5">
                @if (empty($vols))
                    <x-empty icon="plane" :title="$sens === 'departs' ? 'Aucun départ à afficher' : 'Aucune arrivée à afficher'" />
                @else
                    <div class="card overflow-x-auto">
                        <table class="table-admin">
                            <thead>
                                <tr>
                                    <th>Heure</th>
                                    <th>{{ $sens === 'departs' ? 'Destination' : 'Provenance' }}</th>
                                    <th>Vol</th>
                                    <th>Compagnie</th>
                                    <th>{{ $sens === 'departs' ? 'Terminal / porte' : 'Terminal / tapis' }}</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vols as $v)
                                    <tr x-show="!filtre || @js(mb_strtolower($v['ville'].' '.$v['iata'].' '.$v['compagnie'].' '.$v['numero'])).includes(filtre.toLowerCase())">
                                        <td class="whitespace-nowrap font-semibold tabular-nums">
                                            {{ $v['heure'] }}
                                            @if ($v['heure_revisee'])<span class="block text-xs font-normal {{ $v['en_retard'] ? 'text-danger' : 'text-garde' }}">→ {{ $v['heure_revisee'] }}</span>@endif
                                        </td>
                                        <td><span class="font-medium">{{ $v['ville'] }}</span>@if ($v['iata'])<span class="ml-1 text-xs text-muted">{{ $v['iata'] }}</span>@endif</td>
                                        <td class="whitespace-nowrap tabular-nums">{{ $v['numero'] }}</td>
                                        <td>{{ $v['compagnie'] }}</td>
                                        <td class="whitespace-nowrap text-ink/70">{{ $v['terminal'] ?? '—' }}{{ $v['porte'] ? ' · '.$v['porte'] : '' }}</td>
                                        <td><span class="{{ $statuts[$v['statut']][1] }}">{{ $statuts[$v['statut']][0] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach

        <p class="mt-6 text-xs text-muted">Données AeroDataBox, actualisées toutes les 5 minutes. Seule votre compagnie aérienne fait foi.</p>
    </div>
@endsection
