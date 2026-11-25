@extends('layouts.admin')
@section('title', 'Communes')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ route('admin.communes.store') }}" class="card h-fit space-y-4 p-5">
        @csrf
        <h2 class="font-sans text-base font-semibold">Nouvelle commune / ville</h2>
        <div><label class="label">Nom</label><input name="designation" value="{{ old('designation') }}" class="input" required></div>
        <div><label class="label">Lien (vide = automatique)</label><input name="lien" class="input"></div>
        <button class="btn-ink w-full">Ajouter</button>
    </form>
    <div class="lg:col-span-2">
        <form method="GET" class="mb-4 flex gap-2"><input name="q" value="{{ $recherche }}" class="input w-64" placeholder="Rechercher"><button class="btn-line"><x-icon name="search" class="size-4" /></button></form>
        <div class="card overflow-x-auto">
            <table class="table-admin">
                <thead><tr><th>Commune</th><th>Lien</th><th class="text-right">Fiches</th><th></th></tr></thead>
                <tbody>
                    @foreach ($communes as $c)
                        <tr x-data="{ edit: false }">
                            <td>
                                <span x-show="!edit" class="font-medium">{{ $c->DESIGNATION }}</span>
                                <form x-show="edit" x-cloak method="POST" action="{{ route('admin.communes.update', $c->ID) }}" class="flex flex-wrap gap-2">
                                    @csrf @method('PUT')
                                    <input name="designation" value="{{ $c->DESIGNATION }}" class="input w-44 py-1.5" required>
                                    <input name="lien" value="{{ $c->LIEN }}" class="input w-40 py-1.5">
                                    <button class="btn-ink btn-sm">OK</button>
                                </form>
                            </td>
                            <td class="text-xs text-muted">{{ $c->LIEN }}</td>
                            <td class="text-right tabular-nums">{{ number_format($comptes[$c->ID] ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-right whitespace-nowrap">
                                <button type="button" class="btn-line btn-sm" @click="edit = !edit"><x-icon name="pencil" class="size-3.5" /></button>
                                <x-admin.delete-button :action="route('admin.communes.destroy', $c->ID)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $communes->links() }}
    </div>
</div>
@endsection
