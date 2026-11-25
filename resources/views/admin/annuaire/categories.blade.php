@extends('layouts.admin')
@section('title', 'Catégories de l\'annuaire')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ route('admin.categories.store') }}" class="card h-fit space-y-4 p-5">
        @csrf
        <h2 class="font-sans text-base font-semibold">Nouvelle catégorie</h2>
        <div><label class="label">Nom</label><input name="designation" value="{{ old('designation') }}" class="input" required></div>
        <div><label class="label">Lien (laisser vide = automatique)</label><input name="lien" class="input" placeholder="sante"></div>
        <div><label class="label">Description</label><textarea name="description" rows="2" class="input">{{ old('description') }}</textarea></div>
        <div><label class="label">Mots-clés</label><input name="mots_cles" value="{{ old('mots_cles') }}" class="input"></div>
        <button class="btn-ink w-full">Ajouter</button>
    </form>

    <div class="card overflow-x-auto lg:col-span-2">
        <table class="table-admin">
            <thead><tr><th>Catégorie</th><th>Lien</th><th class="text-right">Sous-cat.</th><th class="text-right">Entreprises</th><th></th></tr></thead>
            <tbody>
                @foreach ($categories as $c)
                    <tr x-data="{ edit: false }">
                        <td>
                            <span x-show="!edit" class="font-medium">{{ $c->DESIGNATION }}</span>
                            <form x-show="edit" x-cloak method="POST" action="{{ route('admin.categories.update', $c->ID) }}" class="grid gap-2">
                                @csrf @method('PUT')
                                <input name="designation" value="{{ $c->DESIGNATION }}" class="input py-1.5" required>
                                <input name="lien" value="{{ $c->LIEN }}" class="input py-1.5">
                                <textarea name="description" rows="2" class="input py-1.5" placeholder="Description">{{ $c->DESCRIPTION }}</textarea>
                                <input name="mots_cles" value="{{ $c->MOTS_CLES }}" class="input py-1.5" placeholder="Mots-clés">
                                <div class="flex gap-2"><button class="btn-ink btn-sm">Enregistrer</button><button type="button" class="btn-ghost btn-sm" @click="edit = false">Annuler</button></div>
                            </form>
                        </td>
                        <td class="text-xs text-muted">/annuaire/{{ $c->LIEN }}</td>
                        <td class="text-right"><a href="{{ route('admin.sous-categories.index', ['categorie' => $c->ID]) }}" class="link">{{ $c->sous_categories_count }}</a></td>
                        <td class="text-right tabular-nums">{{ number_format($entreprises[$c->ID] ?? 0, 0, ',', ' ') }}</td>
                        <td class="text-right whitespace-nowrap">
                            <button type="button" class="btn-line btn-sm" @click="edit = !edit"><x-icon name="pencil" class="size-3.5" /></button>
                            <x-admin.delete-button :action="route('admin.categories.destroy', $c->ID)" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
