@extends('layouts.admin')
@section('title', 'Biens immobiliers')

@section('content')
<nav class="mb-4 flex flex-wrap gap-1 border-b border-line">
    @foreach (['attente' => 'En attente', 'valides' => 'Validés', 'tous' => 'Tous'] as $cle => $label)
        <a href="{{ route('admin.biens.index', array_merge(request()->except('page'), ['statut' => $cle])) }}"
           class="-mb-px border-b-2 px-3 py-2 text-sm font-medium {{ $statut === $cle ? 'border-ink text-ink' : 'border-transparent text-muted hover:text-ink' }}">
            {{ $label }} <span class="ml-1 rounded-full bg-sand px-2 py-0.5 text-xs">{{ number_format($compteurs[$cle], 0, ',', ' ') }}</span>
        </a>
    @endforeach
</nav>

<x-admin.toolbar>
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="statut" value="{{ $statut }}">
        <div><label class="label">Titre ou n°</label><input name="q" value="{{ $recherche }}" class="input w-64"></div>
        <div><label class="label">Type</label>
            <select name="type" class="input w-56"><option value="">Tous</option>@foreach ($types as $t)<option value="{{ $t->ID }}" @selected(request('type') == $t->ID)>{{ $t->DESIGNATION }}</option>@endforeach</select>
        </div>
        <button class="btn-line"><x-icon name="search" class="size-4" /> Filtrer</button>
    </form>
</x-admin.toolbar>

<div class="card overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th class="w-20"></th><th>Bien</th><th>Prix</th><th>Annonceur</th><th>Statut</th><th></th></tr></thead>
        <tbody>
            @forelse ($biens as $b)
                <tr>
                    <td><img src="{{ $b->image_url }}" alt="" class="h-12 w-16 rounded object-cover bg-sand" loading="lazy"></td>
                    <td>
                        <a href="{{ route('admin.biens.edit', $b->ID) }}" class="font-medium hover:underline">{{ $b->entreprise?->TITRE ?? 'Bien #'.$b->ID }}</a>
                        <p class="text-xs text-muted">n° {{ $b->ID }} · {{ $b->type?->DESIGNATION }} · {{ $b->categorie?->DESIGNATION }} · {{ $b->entreprise?->commune?->DESIGNATION }} · {{ $b->DATE_LOG }}</p>
                    </td>
                    <td class="whitespace-nowrap text-xs">{{ $b->prix_affiche }}</td>
                    <td class="text-xs">{{ $b->auteur?->name ?? '—' }}</td>
                    <td class="whitespace-nowrap">
                        <form method="POST" action="{{ route('admin.biens.validation', $b->ID) }}" class="inline">@csrf @method('PATCH')
                            @if ($b->VALIDATION === 'OUI')
                                <button class="tag-garde" title="Cliquer pour retirer du site">En ligne</button>
                            @else
                                <button class="btn-garde btn-sm" title="Publier sur le site"><x-icon name="check" class="size-3.5" /> Valider</button>
                            @endif
                        </form>
                        @foreach (['FLASH' => 'Flash', 'LUXE' => 'Luxe'] as $col => $label)
                            <form method="POST" action="{{ route('admin.biens.drapeau', [$b->ID, $col]) }}" class="inline">@csrf @method('PATCH')
                                <button class="{{ strtoupper((string) $b->{$col}) === 'OUI' ? 'tag-amber' : 'tag opacity-60 hover:opacity-100' }}">{{ $label }}</button>
                            </form>
                        @endforeach
                    </td>
                    <td class="text-right whitespace-nowrap">
                        @if ($b->entreprise?->LIEN)<a href="{{ url('/immobilier/proprietes-'.$b->entreprise->LIEN) }}" target="_blank" class="btn-line btn-sm" title="Voir"><x-icon name="eye" class="size-3.5" /></a>@endif
                        <a href="{{ route('admin.biens.edit', $b->ID) }}" class="btn-line btn-sm"><x-icon name="pencil" class="size-3.5" /></a>
                        <x-admin.delete-button :action="route('admin.biens.destroy', [$b->ID, 'statut' => $statut])" message="Supprimer ce bien et toutes ses photos ?" />
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty icon="home" title="Aucun bien" /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $biens->links() }}
@endsection
