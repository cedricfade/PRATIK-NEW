@extends('layouts.admin')
@section('title', 'News web')

@section('content')
<div class="grid gap-6 xl:grid-cols-3">
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.news-web.store') }}" class="card h-fit space-y-4 p-5">
        @csrf
        <h2 class="font-sans text-base font-semibold">Ajouter un article</h2>
        <div><label class="label">Titre</label><input name="titre" value="{{ old('titre') }}" class="input" required></div>
        <div><label class="label">Lien de l'article</label><input type="url" name="lien_web" value="{{ old('lien_web') }}" class="input" placeholder="https://" required></div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Source</label><select name="source_web_id" class="input" required>@foreach ($sources as $s)<option value="{{ $s->ID }}" @selected(old('source_web_id') == $s->ID)>{{ $s->DESIGNATION }}</option>@endforeach</select></div>
            <div><label class="label">Date</label><input type="date" name="date_parution" value="{{ old('date_parution', now()->toDateString()) }}" class="input" required></div>
        </div>
        <div><label class="label">Résumé</label><textarea name="resume" rows="4" class="input">{{ old('resume') }}</textarea></div>
        <div><label class="label">Image</label><input type="file" name="image" accept="image/*" class="text-sm"></div>
        <button class="btn-ink w-full">Publier</button>
    </form>

    <div class="xl:col-span-2">
        <form method="GET" class="mb-4 flex flex-wrap gap-2">
            <input name="q" value="{{ $recherche }}" class="input w-64" placeholder="Titre">
            <select name="source" class="input w-52"><option value="">Toutes les sources</option>@foreach ($sources as $s)<option value="{{ $s->ID }}" @selected(request('source') == $s->ID)>{{ $s->DESIGNATION }}</option>@endforeach</select>
            <button class="btn-line"><x-icon name="search" class="size-4" /></button>
        </form>
        <div class="card overflow-x-auto">
            <table class="table-admin">
                <thead><tr><th class="w-16"></th><th>Article</th><th>Source</th><th>Date</th><th></th></tr></thead>
                <tbody>
                    @forelse ($news as $n)
                        <tr>
                            <td>@if ($n->IMAGE)<img src="{{ $n->image_url }}" alt="" class="h-10 w-14 rounded object-cover bg-sand" loading="lazy">@endif</td>
                            <td><a href="{{ $n->LIEN_WEB }}" target="_blank" rel="noopener" class="font-medium hover:underline">{{ $n->entreprise?->TITRE }}</a></td>
                            <td class="text-xs">{{ $n->sourceWeb?->DESIGNATION }}</td>
                            <td class="text-xs whitespace-nowrap">{{ $n->DATE_PARUTION }}</td>
                            <td class="text-right"><x-admin.delete-button :action="route('admin.news-web.destroy', $n->ID)" message="Supprimer cet article et son image ?" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty icon="globe" title="Aucun article" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $news->links() }}
    </div>
</div>
@endsection
