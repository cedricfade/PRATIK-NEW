@extends('layouts.admin')
@section('title', 'Messages reçus')

@section('content')
<nav class="mb-4 flex gap-1 border-b border-line">
    @foreach (['contacts' => 'Formulaires du site', 'entreprises' => 'Messages aux annonceurs'] as $cle => $label)
        <a href="{{ route('admin.messages.index', ['onglet' => $cle]) }}" class="-mb-px border-b-2 px-3 py-2 text-sm font-medium {{ $onglet === $cle ? 'border-ink text-ink' : 'border-transparent text-muted hover:text-ink' }}">{{ $label }}</a>
    @endforeach
</nav>

<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input type="hidden" name="onglet" value="{{ $onglet }}">
    <input name="q" value="{{ $recherche }}" class="input w-64" placeholder="Nom, e-mail ou objet">
    @if ($categories->isNotEmpty())
        <select name="categorie" class="input w-48"><option value="">Toutes catégories</option>@foreach ($categories as $c)<option value="{{ $c }}" @selected(request('categorie') === $c)>{{ $c }}</option>@endforeach</select>
    @endif
    <button class="btn-line"><x-icon name="search" class="size-4" /></button>
</form>

<form method="POST" action="{{ route('admin.messages.destroy-many', $onglet) }}" x-data="{ ids: [] }" onsubmit="return confirm('Supprimer les messages sélectionnés ?')">
    @csrf @method('DELETE')
    <div class="mb-2 flex items-center gap-3 text-sm">
        <label class="flex items-center gap-2"><input type="checkbox" class="rounded border-line" @change="ids = $event.target.checked ? [...$root.querySelectorAll('input[name=\'ids[]\']')].map(i => i.value) : []"> Tout sélectionner</label>
        <button class="btn-line btn-sm text-danger" :disabled="ids.length === 0"><x-icon name="trash" class="size-3.5" /> Supprimer (<span x-text="ids.length"></span>)</button>
        <span class="ml-auto text-muted">{{ number_format($messages->total(), 0, ',', ' ') }} message(s)</span>
    </div>
    <div class="card divide-y divide-line">
        @forelse ($messages as $m)
            <div x-data="{ ouvert: false }" class="px-4 py-3">
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="ids[]" value="{{ $m->id }}" x-model="ids" class="mt-1 rounded border-line">
                    <button type="button" class="min-w-0 flex-1 text-left" @click="ouvert = !ouvert">
                        <p class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="font-semibold">{{ trim(($m->nom ?? '').' '.($m->prenom ?? '')) ?: 'Anonyme' }}</span>
                            <span class="text-muted">{{ $m->email }}</span>
                            @if ($onglet === 'contacts' && $m->categorie)<span class="tag">{{ $m->categorie }}</span>@endif
                            @if ($onglet === 'entreprises')<span class="tag">→ {{ $m->entreprise?->TITRE ?? 'n° '.$m->entreprise_id }}</span>@if ($m->reponse)<span class="tag-garde">répondu</span>@endif @endif
                        </p>
                        <p class="truncate text-sm text-ink/80">{{ $m->objet }}</p>
                    </button>
                    <span class="shrink-0 text-xs text-muted">{{ $onglet === 'contacts' ? trim($m->date.' '.substr((string) $m->heure, 0, 5)) : $m->created_at?->format('d/m/Y H:i') }}</span>
                </div>
                <div x-show="ouvert" x-collapse x-cloak class="mt-3 ml-7 rounded-lg bg-paper p-3 text-sm">
                    @if ($m->contact)<p class="mb-2 text-muted">Téléphone : <a href="{{ tel_link($m->contact) }}" class="link">{{ $m->contact }}</a></p>@endif
                    <p class="whitespace-pre-line">{{ \Illuminate\Support\Str::limit($onglet === 'contacts' ? $m->message : $m->contactmessage, 3000) }}</p>
                    @if ($onglet === 'entreprises' && $m->reponse)<p class="mt-3 border-t border-line pt-3 text-ink/70"><strong>Réponse :</strong> {{ $m->reponse }}</p>@endif
                    <div class="mt-3 flex gap-2">
                        @if ($m->email)<a href="mailto:{{ $m->email }}?subject={{ rawurlencode('Re: '.$m->objet) }}" class="btn-line btn-sm"><x-icon name="mail" class="size-3.5" /> Répondre</a>@endif
                        <button form="suppr-{{ $m->id }}" class="btn-line btn-sm text-danger"><x-icon name="trash" class="size-3.5" /> Supprimer</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-6"><x-empty icon="inbox" title="Aucun message" /></div>
        @endforelse
    </div>
</form>
@foreach ($messages as $m)
    <form id="suppr-{{ $m->id }}" method="POST" action="{{ route('admin.messages.destroy', [$onglet, $m->id]) }}" onsubmit="return confirm('Supprimer ce message ?')" class="hidden">@csrf @method('DELETE')</form>
@endforeach
{{ $messages->links() }}
@endsection
