@extends('layouts.site')
@section('title', 'Mot de passe oublié')
@section('noindex', true)

@section('content')
<div class="container-page py-12 lg:py-20">
    <div class="card mx-auto w-full max-w-md p-6 sm:p-8">
        <h1 class="text-2xl font-semibold">Mot de passe oublié</h1>
        <p class="mt-2 text-sm text-muted">Indiquez l'adresse e-mail de votre compte : nous vous envoyons un lien pour choisir un nouveau mot de passe.</p>
        @if (session('status'))<p class="mt-4 rounded-md bg-garde-wash px-3 py-2 text-sm text-garde">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="label" for="email">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input">
                @error('email')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <button class="btn-ink w-full py-3">Envoyer le lien</button>
        </form>
    </div>
</div>
@endsection
