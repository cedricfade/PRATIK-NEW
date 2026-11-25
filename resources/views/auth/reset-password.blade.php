@extends('layouts.site')
@section('title', 'Nouveau mot de passe')
@section('noindex', true)

@section('content')
<div class="container-page py-12 lg:py-20">
    <div class="card mx-auto w-full max-w-md p-6 sm:p-8">
        <h1 class="text-2xl font-semibold">Choisir un nouveau mot de passe</h1>
        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="label" for="email">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required class="input">
                @error('email')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password">Nouveau mot de passe</label>
                <input id="password" type="password" name="password" required class="input" autocomplete="new-password">
                @error('password')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password_confirmation">Confirmation</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="input" autocomplete="new-password">
            </div>
            <button class="btn-ink w-full py-3">Enregistrer</button>
        </form>
    </div>
</div>
@endsection
