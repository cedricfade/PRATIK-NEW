@extends('layouts.site')
@section('title', 'Créer un compte')
@section('noindex', true)

@section('content')
<div class="container-page py-12 lg:py-20">
    <div class="card mx-auto w-full max-w-lg p-6 sm:p-8">
        <h1 class="text-2xl font-semibold">Créer un compte</h1>
        <p class="mt-1 text-sm text-muted">Vous êtes une entreprise ? <a href="{{ route('souscription') }}" class="link text-ink">Inscrivez-la dans l'annuaire</a>.</p>
        <form method="POST" action="{{ route('register') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2">
                <label class="label" for="name">Nom complet</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="input">
                @error('name')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input">
                @error('email')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="telephone">Téléphone <span class="font-normal text-muted">(facultatif)</span></label>
                <input id="telephone" name="telephone" value="{{ old('telephone') }}" class="input" placeholder="07 00 00 00 00">
            </div>
            <div>
                <label class="label" for="password">Mot de passe</label>
                <input id="password" type="password" name="password" required class="input" autocomplete="new-password">
                @error('password')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password_confirmation">Confirmation</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="input" autocomplete="new-password">
            </div>
            <button class="btn-ink py-3 sm:col-span-2">Créer mon compte</button>
        </form>
        <p class="mt-6 text-center text-sm text-muted">Déjà inscrit ? <a href="{{ route('login') }}" class="link text-ink">Se connecter</a></p>
    </div>
</div>
@endsection
