@extends('layouts.admin')
@section('title', 'Vidéos')

@section('content')
<form method="POST" action="{{ route('admin.videos.store') }}" class="card mb-6 grid gap-3 p-5 md:grid-cols-[1fr_1fr_auto] md:items-end">
    @csrf
    <div><label class="label">Titre</label><input name="libelle" value="{{ old('libelle') }}" class="input" required></div>
    <div><label class="label">Lien YouTube</label><input type="url" name="lien" value="{{ old('lien') }}" class="input" placeholder="https://www.youtube.com/watch?v=…" required></div>
    <button class="btn-ink"><x-icon name="plus" class="size-4" /> Ajouter</button>
</form>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @forelse ($videos as $video)
        <article class="card overflow-hidden" x-data="{ edit: false }">
            @if ($video->vignette)<img src="{{ $video->vignette }}" alt="" class="aspect-video w-full object-cover" loading="lazy">@endif
            <div class="p-3">
                <p x-show="!edit" class="line-clamp-2 text-sm font-medium">{{ $video->libelle }}</p>
                <form x-show="edit" x-cloak method="POST" action="{{ route('admin.videos.update', $video->id) }}" class="space-y-2">
                    @csrf @method('PUT')
                    <input name="libelle" value="{{ $video->libelle }}" class="input py-1.5" required>
                    <input name="lien" value="{{ $video->lien }}" class="input py-1.5" required>
                    <button class="btn-ink btn-sm">Enregistrer</button>
                </form>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-xs text-muted">{{ $video->created_at?->format('d/m/Y') }}</span>
                    <span class="flex gap-1">
                        <button type="button" class="btn-line btn-sm" @click="edit = !edit"><x-icon name="pencil" class="size-3.5" /></button>
                        <x-admin.delete-button :action="route('admin.videos.destroy', $video->id)" />
                    </span>
                </div>
            </div>
        </article>
    @empty
        <div class="col-span-full"><x-empty icon="play" title="Aucune vidéo" /></div>
    @endforelse
</div>
{{ $videos->links() }}
@endsection
