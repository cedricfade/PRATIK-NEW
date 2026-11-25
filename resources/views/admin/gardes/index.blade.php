@extends('layouts.admin')
@section('title', 'Pharmacies de garde')

@section('content')
{{-- Choix de la période --}}
<div class="card mb-5 flex flex-wrap items-end gap-4 p-4">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="label" for="periode">Période de garde</label>
            <select id="periode" name="periode" class="input w-80" onchange="this.form.submit()">
                @foreach ($periodes as $p)
                    <option value="{{ $p->ID }}" @selected($periode?->ID === $p->ID)>
                        {{ \Carbon\Carbon::parse($p->DEBUT)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($p->FIN)->format('d/m/Y') }}
                        @if ($p->DEBUT <= now()->toDateString() && $p->FIN >= now()->toDateString()) (en cours) @endif
                    </option>
                @endforeach
            </select>
        </div>
        <noscript><button class="btn-line">Afficher</button></noscript>
    </form>
    <a href="{{ route('admin.periodes.index') }}" class="btn-line"><x-icon name="calendar" class="size-4" /> Créer / gérer les périodes</a>
    @if ($periode)
        <p class="ml-auto text-sm text-muted">Semaine {{ $periode->libelle }} · <strong class="text-ink">{{ $gardes->count() }}</strong> pharmacie(s) de garde</p>
    @endif
</div>

@if (! $periode)
    <x-empty icon="calendar" title="Aucune période de garde">Commencez par <a href="{{ route('admin.periodes.index') }}" class="link">créer une période</a>.</x-empty>
@else
<div class="grid gap-6 xl:grid-cols-5">
    {{-- Ajouter des pharmacies --}}
    <section class="card p-5 xl:col-span-3">
        <h2 class="font-sans text-base font-semibold">Ajouter des pharmacies à cette période</h2>
        <form method="GET" class="mt-3 flex flex-wrap items-end gap-3">
            <input type="hidden" name="periode" value="{{ $periode->ID }}">
            <div>
                <label class="label" for="commune">Commune</label>
                <select id="commune" name="commune" class="input w-56" onchange="this.form.submit()">
                    <option value="">— Choisir —</option>
                    @foreach ($communes as $c)
                        <option value="{{ $c->ID }}" @selected($communeId === (int) $c->ID)>{{ $c->DESIGNATION }} ({{ $c->total }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="label" for="q">ou rechercher par nom</label>
                <input id="q" name="q" value="{{ $recherche }}" class="input" placeholder="Pharmacie de la Paix…">
            </div>
            <button class="btn-line"><x-icon name="search" class="size-4" /> Filtrer</button>
        </form>

        @if ($pharmacies->isEmpty())
            <x-empty class="mt-4" icon="filter" title="{{ ($communeId || $recherche) ? 'Aucune pharmacie trouvée' : 'Choisissez une commune' }}">
                @unless ($communeId || $recherche) Sélectionnez une commune (ou tapez un nom) pour afficher ses pharmacies. @endunless
            </x-empty>
        @else
            <form method="POST" action="{{ route('admin.gardes.store') }}" class="mt-4" x-data="{ coches: [] }">
                @csrf
                <input type="hidden" name="periode_id" value="{{ $periode->ID }}">
                <div class="mb-2 flex items-center justify-between text-sm">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" class="rounded border-line" @change="coches = $event.target.checked ? [...$root.querySelectorAll('input[name=\'entreprises[]\']:not(:disabled)')].map(i => i.value) : []">
                        Tout cocher
                    </label>
                    <span class="text-muted"><span x-text="coches.length">0</span> sélectionnée(s)</span>
                </div>
                <div class="max-h-[60vh] overflow-y-auto rounded-lg border border-line">
                    <table class="table-admin">
                        <tbody>
                            @foreach ($pharmacies as $ph)
                                @php($deja = isset($dejaDeGarde[$ph->ID]))
                                <tr class="{{ $deja ? 'bg-garde-wash/50' : '' }}">
                                    <td class="w-8"><input type="checkbox" name="entreprises[]" value="{{ $ph->ID }}" x-model="coches" class="rounded border-line" @disabled($deja)></td>
                                    <td>
                                        <p class="font-medium">{{ $ph->TITRE }}</p>
                                        <p class="text-xs text-muted">{{ $ph->COMMUNE }}@if ($ph->ADRESSE) · {{ \Illuminate\Support\Str::limit($ph->ADRESSE, 60) }}@endif</p>
                                    </td>
                                    <td class="text-xs text-muted">{{ $ph->TEL_1 }}</td>
                                    <td class="text-right">@if ($deja)<span class="tag-garde">De garde</span>@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($pharmacies->count() >= 300)<p class="mt-2 text-xs text-muted">Affichage limité à 300 résultats : affinez avec la commune ou le nom.</p>@endif
                <button class="btn-garde mt-4" :disabled="coches.length === 0"><x-icon name="plus" class="size-4" /> Mettre de garde (<span x-text="coches.length">0</span>)</button>
            </form>
        @endif
    </section>

    {{-- Gardes de la période --}}
    <section class="card p-5 xl:col-span-2">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-sans text-base font-semibold">De garde sur cette période</h2>
            @if ($gardes->isNotEmpty())
                <x-admin.delete-button :action="route('admin.gardes.vider', $periode->ID)" label="Vider" :message="'Retirer les '.$gardes->count().' pharmacies de garde de cette période ?'" />
            @endif
        </div>
        @forelse ($gardes->groupBy('COMMUNE') as $commune => $liste)
            <h3 class="mt-4 mb-1 flex items-center justify-between font-sans text-xs font-semibold tracking-wide text-ink/60 uppercase">
                {{ $commune }} <span>{{ $liste->count() }}</span>
            </h3>
            <ul class="divide-y divide-line rounded-lg border border-line text-sm">
                @foreach ($liste as $g)
                    <li class="flex items-center justify-between gap-2 px-3 py-1.5">
                        <span class="truncate">{{ $g->TITRE }}</span>
                        <form method="POST" action="{{ route('admin.gardes.destroy', $g->ID) }}">
                            @csrf @method('DELETE')
                            <button class="rounded p-1 text-muted hover:bg-red-50 hover:text-danger" title="Retirer"><x-icon name="x" class="size-4" /></button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @empty
            <x-empty class="mt-4" icon="cross" title="Aucune pharmacie de garde">Cochez des pharmacies dans la liste à gauche.</x-empty>
        @endforelse
    </section>
</div>
@endif
@endsection
