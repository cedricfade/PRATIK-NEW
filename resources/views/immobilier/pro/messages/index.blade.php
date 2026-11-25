@extends('layouts.immo')

@section('title', 'Messages reçus')
@section('noindex', true)

@section('content')
    <x-immo.pro-nav />

    <div class="container-page py-8">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('immo.pro.messages.index') }}" @class(['rounded-full border px-3.5 py-1.5 font-medium', 'border-ink bg-ink text-white' => ! $filtre, 'border-line bg-white text-ink/75' => $filtre])>Tous</a>
            <a href="{{ route('immo.pro.messages.index', ['filtre' => 'non-lus']) }}" @class(['rounded-full border px-3.5 py-1.5 font-medium', 'border-ink bg-ink text-white' => $filtre, 'border-line bg-white text-ink/75' => ! $filtre])>Non lus</a>
        </div>

        @if ($messages->isEmpty())
            <x-empty icon="inbox" title="Aucun message" class="mt-6">Les demandes envoyées depuis vos annonces arrivent ici, et par e-mail.</x-empty>
        @else
            <div class="card mt-5 divide-y divide-line/70">
                @foreach ($messages as $m)
                    <a href="{{ route('immo.pro.messages.show', $m->id) }}" class="flex gap-4 px-5 py-4 hover:bg-paper">
                        <span class="mt-2 size-2 shrink-0 rounded-full {{ $m->status == 0 ? 'bg-roof' : 'bg-transparent' }}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4">
                                <p class="{{ $m->status == 0 ? 'font-semibold' : 'font-medium' }}">{{ trim($m->prenom.' '.$m->nom) }}</p>
                                <p class="text-xs text-muted">{{ $m->created_at?->locale('fr')->isoFormat('D MMM YYYY, HH:mm') }}</p>
                            </div>
                            <p class="text-sm text-ink/80">{{ $m->objet }}</p>
                            <p class="mt-0.5 line-clamp-1 text-sm text-muted">{{ $m->contactmessage }}</p>
                        </div>
                        @if ($m->status == 2)<span class="tag-garde h-fit shrink-0">Répondu</span>@endif
                    </a>
                @endforeach
            </div>
            {{ $messages->links() }}
        @endif
    </div>
@endsection
