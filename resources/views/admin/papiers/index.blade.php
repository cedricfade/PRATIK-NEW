@extends('layouts.admin')
@section('title', 'Papiers administratifs')

@section('content')
<div class="grid gap-6 xl:grid-cols-5">
    <form method="POST" enctype="multipart/form-data" action="{{ $edit ? route('admin.papiers.update', $edit->ID) : route('admin.papiers.store') }}" class="card h-fit space-y-4 p-5 xl:col-span-2"
          x-data="{ pieces: @js(old('pieces', $edit?->pieces->pluck('LIBELLE')->all() ?: [''])) }">
        @csrf
        @if ($edit) @method('PUT') @endif
        <div class="flex items-center justify-between">
            <h2 class="font-sans text-base font-semibold">{{ $edit ? 'Modifier la fiche' : 'Nouvelle fiche' }}</h2>
            @if ($edit)<a href="{{ route('admin.papiers.index') }}" class="text-sm text-muted hover:text-ink">Annuler</a>@endif
        </div>
        <div><label class="label">Titre</label><input name="titre" value="{{ old('titre', $edit?->TITRE) }}" class="input" required placeholder="Passeport, CNI, extrait de naissance…"></div>
        <div><label class="label">Ville / commune</label>
            <select name="commune_id" class="input"><option value="">Document national (toutes les villes)</option>@foreach ($communes as $c)<option value="{{ $c->ID }}" @selected(old('commune_id', $edit?->COMMUNE_ID) == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach</select>
        </div>
        <div><label class="label">Lien officiel</label><input type="url" name="lien" value="{{ old('lien', $edit?->LIEN) }}" class="input" placeholder="https://"></div>
        <div><label class="label">Description</label><textarea name="description" rows="4" class="input">{{ old('description', $edit?->DESCRIPTION) }}</textarea></div>
        <div>
            <label class="label">Pièces à fournir</label>
            <template x-for="(p, i) in pieces" :key="i">
                <div class="mb-2 flex gap-2">
                    <input name="pieces[]" x-model="pieces[i]" class="input py-2" placeholder="Copie de l'acte de naissance…">
                    <button type="button" class="btn-ghost btn-sm" @click="pieces.splice(i, 1)" x-show="pieces.length > 1"><x-icon name="x" class="size-4" /></button>
                </div>
            </template>
            <button type="button" class="btn-line btn-sm" @click="pieces.push('')"><x-icon name="plus" class="size-3.5" /> Ajouter une pièce</button>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Ordre</label><input type="number" min="0" name="ordre" value="{{ old('ordre', $edit?->ORDRE ?? 0) }}" class="input"></div>
            <label class="flex items-center gap-2 pt-7 text-sm"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked(old('active', $edit?->ACTIVE ?? 1)) class="rounded border-line"> Visible</label>
        </div>
        <div class="flex items-center gap-3">
            @if ($edit?->image_url)<img src="{{ $edit->image_url }}" alt="" class="size-14 rounded border border-line object-contain">@endif
            <div><label class="label">Image / logo</label><input type="file" name="image" accept="image/*" class="text-sm"></div>
        </div>
        <button class="btn-ink w-full">{{ $edit ? 'Enregistrer' : 'Ajouter' }}</button>
    </form>

    <div class="card overflow-x-auto xl:col-span-3">
        <table class="table-admin">
            <thead><tr><th></th><th>Fiche</th><th>Portée</th><th class="text-right">Pièces</th><th></th></tr></thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="{{ $edit?->ID === $item->ID ? 'bg-amber-wash/60' : '' }}">
                        <td class="w-12">@if ($item->image_url)<img src="{{ $item->image_url }}" alt="" class="size-9 rounded object-contain">@endif</td>
                        <td><p class="font-medium">{{ $item->TITRE }} @unless ($item->ACTIVE)<span class="tag text-danger">masquée</span>@endunless</p>
                            @if ($item->LIEN)<a href="{{ $item->LIEN }}" target="_blank" rel="noopener" class="text-xs text-muted hover:underline">{{ \Illuminate\Support\Str::limit($item->LIEN, 50) }}</a>@endif</td>
                        <td class="text-xs">{{ $item->commune?->DESIGNATION ?? 'National' }}</td>
                        <td class="text-right text-xs">{{ $item->pieces->count() }}</td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.papiers.edit', $item->ID) }}" class="btn-line btn-sm"><x-icon name="pencil" class="size-3.5" /></a>
                            <x-admin.delete-button :action="route('admin.papiers.destroy', $item->ID)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty icon="file-text" title="Aucune fiche" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
