@extends('layouts.admin')
@section('title', 'Sous-catégories de l\'annuaire')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.sous-categories.store') }}" class="card h-fit space-y-4 p-5">
        @csrf
        <h2 class="font-sans text-base font-semibold">Nouvelle sous-catégorie</h2>
        <div><label class="label">Catégorie</label>
            <select name="categorie_id" class="input" required>@foreach ($categories as $c)<option value="{{ $c->ID }}" @selected(old('categorie_id', request('categorie')) == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach</select>
        </div>
        <div><label class="label">Nom</label><input name="designation" value="{{ old('designation') }}" class="input" required></div>
        <div><label class="label">Lien (vide = automatique)</label><input name="lien" class="input"></div>
        <div><label class="label">Texte de présentation (SEO)</label><textarea name="descriptif" rows="3" class="input">{{ old('descriptif') }}</textarea></div>
        <div><label class="label">Bannière publicitaire</label><input type="file" name="pub" accept="image/*" class="text-sm"></div>
        <div><label class="label">Lien de la bannière</label><input name="lien_pub" value="{{ old('lien_pub') }}" class="input" placeholder="https://"></div>
        <button class="btn-ink w-full">Ajouter</button>
    </form>

    <div class="lg:col-span-2">
        <x-admin.toolbar>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div><label class="label">Catégorie</label>
                    <select name="categorie" class="input w-56" onchange="this.form.submit()"><option value="">Toutes</option>@foreach ($categories as $c)<option value="{{ $c->ID }}" @selected(request('categorie') == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach</select>
                </div>
                <div><label class="label">Nom</label><input name="q" value="{{ $recherche }}" class="input w-56"></div>
                <button class="btn-line"><x-icon name="search" class="size-4" /></button>
            </form>
        </x-admin.toolbar>
        <div class="card overflow-x-auto">
            <table class="table-admin">
                <thead><tr><th>Sous-catégorie</th><th>Catégorie</th><th class="text-right">Entreprises</th><th>Pub</th><th></th></tr></thead>
                <tbody>
                    @forelse ($sousCategories as $s)
                        <tr>
                            <td><span class="font-medium">{{ $s->DESIGNATION }}</span>@unless ($s->ACTIVATION)<span class="tag ml-1 text-danger">inactive</span>@endunless
                                <p class="text-xs text-muted">/annuaire/{{ $s->categorie?->LIEN }}/{{ $s->LIEN }}</p></td>
                            <td class="text-xs">{{ $s->categorie?->DESIGNATION }}</td>
                            <td class="text-right tabular-nums">{{ number_format($comptes[$s->ID] ?? 0, 0, ',', ' ') }}</td>
                            <td>@if ($s->PUBS)<x-icon name="image" class="size-4 text-garde" />@endif</td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('admin.sous-categories.edit', $s->ID) }}" class="btn-line btn-sm"><x-icon name="pencil" class="size-3.5" /></a>
                                <x-admin.delete-button :action="route('admin.sous-categories.destroy', $s->ID)" />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty title="Aucune sous-catégorie" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $sousCategories->links() }}
    </div>
</div>
@endsection
