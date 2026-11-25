@extends('layouts.admin')
@section('title', 'Sous-catégorie : '.$sous->DESIGNATION)

@section('content')
<a href="{{ route('admin.sous-categories.index', ['categorie' => $sous->CATEGORIE_ID]) }}" class="text-sm text-muted hover:text-ink">← Retour</a>
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.sous-categories.update', $sous->ID) }}" class="card mt-4 grid max-w-3xl gap-4 p-5 md:grid-cols-2">
    @csrf @method('PUT')
    <div><label class="label">Catégorie</label>
        <select name="categorie_id" class="input">@foreach ($categories as $c)<option value="{{ $c->ID }}" @selected(old('categorie_id', $sous->CATEGORIE_ID) == $c->ID)>{{ $c->DESIGNATION }}</option>@endforeach</select>
    </div>
    <div><label class="label">Nom</label><input name="designation" value="{{ old('designation', $sous->DESIGNATION) }}" class="input" required></div>
    <div><label class="label">Lien</label><input name="lien" value="{{ old('lien', $sous->LIEN) }}" class="input"><p class="mt-1 text-xs text-amber-deep">Le modifier change l'URL publique.</p></div>
    <label class="flex items-center gap-2 pt-7 text-sm"><input type="hidden" name="activation" value="0"><input type="checkbox" name="activation" value="1" @checked(old('activation', $sous->ACTIVATION)) class="rounded border-line"> Visible sur le site</label>
    <div class="md:col-span-2"><label class="label">Texte de présentation</label><textarea name="descriptif" rows="4" class="input">{{ old('descriptif', $sous->DESCRIPTIF) }}</textarea></div>
    <div class="md:col-span-2"><label class="label">Balises meta / description SEO</label><textarea name="metas" rows="3" class="input">{{ old('metas', $sous->METAS) }}</textarea></div>
    <div class="md:col-span-2 flex flex-wrap items-start gap-4 rounded-lg border border-line p-4">
        @if ($sous->pub_url)<img src="{{ $sous->pub_url }}" alt="" class="h-24 rounded border border-line object-contain">@endif
        <div class="flex-1 space-y-2">
            <label class="label">Bannière publicitaire</label>
            <input type="file" name="pub" accept="image/*" class="text-sm">
            @if ($sous->PUBS)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="supprimer_pub" value="1" class="rounded border-line"> Retirer la bannière</label>@endif
            <input name="lien_pub" value="{{ old('lien_pub', $sous->LIEN_PUB) }}" class="input" placeholder="Lien de la bannière (https://)">
        </div>
    </div>
    <div class="md:col-span-2 flex justify-end"><button class="btn-ink">Enregistrer</button></div>
</form>
@endsection
