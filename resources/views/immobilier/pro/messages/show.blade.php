@extends('layouts.immo')

@section('title', 'Message de '.trim($message->prenom.' '.$message->nom))
@section('noindex', true)

@section('content')
    <x-immo.pro-nav />

    <div class="container-page max-w-3xl py-8">
        <a href="{{ route('immo.pro.messages.index') }}" class="inline-flex items-center gap-1 text-sm text-muted hover:text-ink"><x-icon name="arrow-left" class="size-4" /> Tous les messages</a>

        <article class="card mt-4 p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-lg font-semibold">{{ trim($message->prenom.' '.$message->nom) }}</p>
                    <p class="text-sm text-muted">{{ $message->objet }}</p>
                </div>
                <p class="text-sm text-muted">{{ $message->created_at?->locale('fr')->isoFormat('dddd D MMMM YYYY, HH:mm') }}</p>
            </div>
            <div class="mt-3 flex flex-wrap gap-2 text-sm">
                <a href="{{ tel_link($message->contact) }}" class="btn-line btn-sm"><x-icon name="phone" class="size-3.5" /> {{ $message->contact }}</a>
                <a href="mailto:{{ $message->email }}" class="btn-line btn-sm"><x-icon name="mail" class="size-3.5" /> {{ $message->email }}</a>
            </div>
            <p class="mt-5 whitespace-pre-line rounded-lg bg-paper p-4 leading-7">{{ $message->contactmessage }}</p>
        </article>

        @if ($message->reponse)
            <div class="mt-4 rounded-xl border border-garde/25 bg-garde-wash/60 p-5">
                <p class="text-sm font-semibold text-garde">Votre réponse</p>
                <p class="mt-2 whitespace-pre-line text-sm leading-6">{{ $message->reponse }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('immo.pro.messages.repondre', $message->id) }}" class="card mt-4 space-y-3 p-6">
            @csrf
            <label class="label" for="reponse">{{ $message->reponse ? 'Envoyer une nouvelle réponse' : 'Répondre par e-mail' }}</label>
            <textarea id="reponse" name="reponse" rows="6" required class="input" placeholder="Bonjour {{ $message->prenom }}, merci pour votre message…">{{ old('reponse') }}</textarea>
            @error('reponse')<p class="input-error">{{ $message }}</p>@enderror
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs text-muted">Envoyé à {{ $message->email }}. La personne pourra vous répondre directement.</p>
                <button class="btn-roof">Envoyer</button>
            </div>
        </form>
    </div>
@endsection
