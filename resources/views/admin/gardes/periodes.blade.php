@extends('layouts.admin')
@section('title', 'Périodes de garde')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ route('admin.periodes.store') }}" class="card h-fit space-y-4 p-5">
        @csrf
        <h2 class="font-sans text-base font-semibold">Nouvelle période</h2>
        <div><label class="label" for="debut">Du</label><input id="debut" type="date" name="debut" value="{{ old('debut', $suggestion['debut']) }}" class="input" required></div>
        <div><label class="label" for="fin">Au</label><input id="fin" type="date" name="fin" value="{{ old('fin', $suggestion['fin']) }}" class="input" required></div>
        <p class="text-xs text-muted">Proposition : la semaine qui suit la dernière période enregistrée.</p>
        <button class="btn-ink w-full">Créer et affecter les pharmacies</button>
    </form>

    <div class="card overflow-hidden lg:col-span-2">
        <table class="table-admin">
            <thead><tr><th>Période</th><th>Gardes</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @foreach ($periodes as $p)
                    @php($enCours = $p->DEBUT <= now()->toDateString() && $p->FIN >= now()->toDateString())
                    <tr x-data="{ edit: false }">
                        <td>
                            <div x-show="!edit">
                                <span class="font-medium">{{ \Carbon\Carbon::parse($p->DEBUT)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($p->FIN)->format('d/m/Y') }}</span>
                                @if ($enCours)<span class="tag-garde ml-2">En cours</span>@endif
                            </div>
                            <form x-show="edit" x-cloak method="POST" action="{{ route('admin.periodes.update', $p->ID) }}" class="flex flex-wrap items-center gap-2">
                                @csrf @method('PUT')
                                <input type="date" name="debut" value="{{ $p->DEBUT }}" class="input w-40 py-1.5" required>
                                <input type="date" name="fin" value="{{ $p->FIN }}" class="input w-40 py-1.5" required>
                                <button class="btn-ink btn-sm">OK</button>
                                <button type="button" class="btn-ghost btn-sm" @click="edit = false">Annuler</button>
                            </form>
                        </td>
                        <td class="tabular-nums">{{ $comptes[$p->DEBUT.'|'.$p->FIN] ?? 0 }}</td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.gardes.index', ['periode' => $p->ID]) }}" class="btn-line btn-sm"><x-icon name="cross" class="size-3.5" /> Gardes</a>
                            <button type="button" class="btn-line btn-sm" @click="edit = !edit" title="Modifier"><x-icon name="pencil" class="size-3.5" /></button>
                            <x-admin.delete-button :action="route('admin.periodes.destroy', $p->ID)" message="Supprimer cette période ?" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 pb-4">{{ $periodes->links() }}</div>
    </div>
</div>
@endsection
