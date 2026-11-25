@extends('layouts.admin')
@section('title', 'Liste des pharmacies')

@section('content')
<x-admin.toolbar>
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div><label class="label">Commune</label>
            <select name="commune" class="input w-56">
                <option value="">Toutes</option>
                @foreach ($communes as $c)<option value="{{ $c->ID }}" @selected(request('commune') == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach
            </select>
        </div>
        <div><label class="label">Nom</label><input name="q" value="{{ $recherche }}" class="input w-64"></div>
        <button class="btn-line"><x-icon name="search" class="size-4" /> Filtrer</button>
    </form>
    <a href="{{ route('admin.entreprises.create', ['pharmacie' => 1]) }}" class="btn-ink ml-auto"><x-icon name="plus" class="size-4" /> Ajouter une pharmacie</a>
</x-admin.toolbar>

<div class="card overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th>Pharmacie</th><th>Commune</th><th>Téléphone</th><th>GPS</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse ($pharmacies as $ph)
                <tr>
                    <td><p class="font-medium">{{ $ph->TITRE }}</p><p class="text-xs text-muted">{{ \Illuminate\Support\Str::limit($ph->ADRESSE, 70) }}</p></td>
                    <td>{{ $ph->COMMUNE ?? '—' }}</td>
                    <td class="text-xs">{{ $ph->TEL_1 ?: $ph->CEL_1 }}</td>
                    <td>@if ((float) $ph->LATITUDE)<x-icon name="check" class="size-4 text-garde" />@else<span class="text-xs text-amber-deep" title="Sans coordonnées : n'apparaît pas dans « autour de moi »">manquant</span>@endif</td>
                    <td>@if ($ph->DE_GARDE)<span class="tag-garde">De garde</span>@endif</td>
                    <td class="text-right"><a href="{{ route('admin.entreprises.edit', $ph->ID) }}" class="btn-line btn-sm"><x-icon name="pencil" class="size-3.5" /> Modifier</a></td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty title="Aucune pharmacie" /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $pharmacies->links() }}
@endsection
