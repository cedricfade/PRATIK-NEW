@extends('layouts.admin')
@section('title', 'Titrologie — unes des journaux')

@section('content')
<div class="grid gap-6 xl:grid-cols-5">
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.titrologie.store') }}" class="card h-fit space-y-4 p-5 xl:col-span-2"
          x-data="{ lignes: [{ id: 1 }], suivant: 2 }">
        @csrf
        <h2 class="font-sans text-base font-semibold">Publier des unes</h2>
        <div><label class="label">Date de parution</label><input type="date" name="date_parution" value="{{ old('date_parution', $date) }}" class="input w-48" required></div>

        <template x-for="(ligne, i) in lignes" :key="ligne.id">
            <div class="space-y-2 rounded-lg border border-line p-3">
                <div class="flex items-center justify-between text-xs font-semibold text-ink/60"><span x-text="'Une n° ' + (i + 1)"></span>
                    <button type="button" class="text-danger" x-show="lignes.length > 1" @click="lignes.splice(i, 1)">retirer</button></div>
                <select :name="'unes[' + ligne.id + '][source_id]'" class="input" required>
                    <option value="">Journal…</option>
                    @foreach ($sources as $s)<option value="{{ $s->ID }}">{{ $s->DESIGNATION }}</option>@endforeach
                </select>
                <div class="grid grid-cols-3 gap-2">
                    <select :name="'unes[' + ligne.id + '][type_news_id]'" class="input col-span-2">
                        @foreach ($typesNews as $t)<option value="{{ $t->ID }}">{{ $t->DESIGNATION }}</option>@endforeach
                    </select>
                    <input type="number" min="0" :name="'unes[' + ligne.id + '][rang]'" :value="i + 1" class="input" title="Ordre d'affichage">
                </div>
                <input type="file" :name="'unes[' + ligne.id + '][image]'" accept="image/*" class="text-sm" required>
            </div>
        </template>

        <div class="flex gap-2">
            <button type="button" class="btn-line" @click="lignes.push({ id: suivant++ })"><x-icon name="plus" class="size-4" /> Une autre une</button>
            <button class="btn-ink flex-1"><x-icon name="upload" class="size-4" /> Publier</button>
        </div>
    </form>

    <section class="xl:col-span-3">
        <div class="mb-4 flex flex-wrap items-end gap-3">
            <form method="GET" class="flex items-end gap-2">
                <div><label class="label">Afficher le</label><input type="date" name="date" value="{{ $date }}" class="input w-48" onchange="this.form.submit()"></div>
            </form>
            <div class="flex flex-wrap gap-1">
                @foreach ($jours as $j)
                    <a href="{{ route('admin.titrologie.index', ['date' => $j->DATE_PARUTION]) }}" class="{{ $j->DATE_PARUTION === $date ? 'tag-amber' : 'tag hover:border-ink/30' }}">
                        {{ \Carbon\Carbon::parse($j->DATE_PARUTION)->format('d/m') }} · {{ $j->total }}
                    </a>
                @endforeach
            </div>
        </div>

        <p class="mb-3 text-sm text-muted">{{ $unes->count() }} une(s) le {{ \Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($unes as $une)
                <figure class="card overflow-hidden">
                    <img src="{{ $une->image_url }}" alt="" class="aspect-[3/4] w-full bg-sand object-cover object-top" loading="lazy">
                    <figcaption class="flex items-start justify-between gap-2 p-2.5 text-sm">
                        <span><span class="font-medium">{{ $une->source?->DESIGNATION }}</span><span class="block text-xs text-muted">{{ $une->typeNews?->DESIGNATION }} · rang {{ $une->RANG }}</span></span>
                        <x-admin.delete-button :action="route('admin.titrologie.destroy', $une->ID)" message="Supprimer cette une et son image ?" />
                    </figcaption>
                </figure>
            @empty
                <div class="col-span-full"><x-empty icon="newspaper" title="Aucune une pour cette date" /></div>
            @endforelse
        </div>
    </section>
</div>
@endsection
