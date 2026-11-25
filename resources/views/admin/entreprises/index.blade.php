@extends('layouts.admin')
@section('title', 'Entreprises')

@section('content')
<x-admin.toolbar>
    <form method="GET" class="flex flex-1 flex-wrap items-end gap-3">
        <div class="min-w-64 flex-1"><label class="label">Recherche (nom, sigle, activité ou n°)</label><input name="q" value="{{ $recherche }}" class="input" placeholder="ex. pharmacie, BICICI, 4518"></div>
        <div><label class="label">Type</label>
            <select name="type" class="input w-40">
                @foreach ($types as $t)<option value="{{ $t->ID }}" @selected($type == $t->ID)>{{ $t->DESIGNATION }}</option>@endforeach
                <option value="tous" @selected($type === 'tous')>Tous</option>
            </select>
        </div>
        <div><label class="label">Commune</label>
            <select name="commune" class="input w-44">
                <option value="">Toutes</option>
                @foreach ($communes as $c)<option value="{{ $c->ID }}" @selected(request('commune') == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 pb-2.5 text-sm"><input type="checkbox" name="vip" value="1" @checked(request('vip') == 1) class="rounded border-line"> VIP (TOP 12)</label>
        <label class="flex items-center gap-2 pb-2.5 text-sm"><input type="checkbox" name="client" value="OUI" @checked(request('client') === 'OUI') class="rounded border-line"> Clients</label>
        <button class="btn-line"><x-icon name="search" class="size-4" /> Filtrer</button>
    </form>
    <a href="{{ route('admin.entreprises.create') }}" class="btn-ink"><x-icon name="plus" class="size-4" /> Nouvelle entreprise</a>
</x-admin.toolbar>

<p class="mb-2 text-sm text-muted">{{ number_format($entreprises->total(), 0, ',', ' ') }} résultat(s)</p>
<div class="card overflow-x-auto">
    <table class="table-admin">
        <thead><tr><th class="w-14"></th><th>Entreprise</th><th>Type</th><th>Commune</th><th>Téléphone</th><th class="text-right">Vues</th><th></th></tr></thead>
        <tbody>
            @forelse ($entreprises as $e)
                <tr>
                    <td><x-logo-tile :src="$e->annuaire?->logo_url" :name="$e->TITRE" size="size-10" /></td>
                    <td>
                        <a href="{{ route('admin.entreprises.edit', $e->ID) }}" class="font-medium hover:underline">{{ $e->TITRE }}</a>
                        @if ($e->annuaire?->CLIENT === 'OUI')<span class="tag-amber ml-1">Pro</span>@endif
                        @if ((int) $e->annuaire?->VIP === 1)<span class="ml-1 inline-flex items-center gap-1 rounded-full bg-amber px-2 py-0.5 text-[10px] font-bold text-ink"><x-icon name="crown" class="size-3" /> VIP</span>@endif
                        @if (! $e->ACTIVATION)<span class="tag ml-1 text-danger">Inactive</span>@endif
                        <p class="text-xs text-muted">n° {{ $e->ID }}@if ($e->SIGLE) · {{ $e->SIGLE }}@endif</p>
                    </td>
                    <td class="text-xs">{{ $e->type?->DESIGNATION }}</td>
                    <td class="text-xs">{{ $e->commune?->DESIGNATION ?? '—' }}</td>
                    <td class="text-xs">{{ $e->annuaire?->TEL_1 ?: $e->annuaire?->CEL_1 }}</td>
                    <td class="text-right text-xs tabular-nums" title="{{ number_format((float) $e->COMPTEUR, 0, ',', ' ') }} réelles + {{ number_format((int) $e->VUES_BONUS, 0, ',', ' ') }} ajoutées">{{ number_format($e->vues_affichees, 0, ',', ' ') }}@if ($e->VUES_BONUS)<span class="block text-[10px] text-amber-deep">dont +{{ number_format((int) $e->VUES_BONUS, 0, ',', ' ') }}</span>@endif</td>
                    <td class="text-right whitespace-nowrap">
                        @if ($e->LIEN && (int) $e->TYPE_ID === 1)
                            <a href="{{ url('/details/'.$e->LIEN) }}" target="_blank" class="btn-line btn-sm" title="Voir sur le site"><x-icon name="eye" class="size-3.5" /></a>
                        @endif
                        <a href="{{ route('admin.entreprises.edit', $e->ID) }}" class="btn-line btn-sm" title="Modifier"><x-icon name="pencil" class="size-3.5" /></a>
                        <x-admin.delete-button :action="route('admin.entreprises.destroy', $e->ID)" :message="'Supprimer « '.$e->TITRE.' » ? Son logo, sa galerie et ses rubriques seront supprimés définitivement.'" />
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-empty title="Aucune entreprise trouvée" /></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $entreprises->links() }}
@endsection
