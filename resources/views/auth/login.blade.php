@extends($espace === 'immo' ? 'layouts.immo' : 'layouts.site')

@section('title', $espace === 'immo' ? 'Espace annonceur' : 'Connexion')
@section('noindex', true)

@section('content')
<div class="container-page grid gap-10 py-12 lg:grid-cols-2 lg:py-20">
    <div class="hidden lg:block">
        @if ($espace === 'immo')
            <p class="eyebrow !text-roof-deep">Espace annonceur</p>
            <h1 class="mt-3 text-4xl font-semibold leading-tight">Publiez et suivez vos annonces immobilières.</h1>
            <ul class="mt-8 space-y-4 text-ink/80">
                <li class="flex gap-3"><x-icon name="check" class="mt-1 size-5 text-roof-deep" /> Ajoutez vos biens avec photos, prix et localisation.</li>
                <li class="flex gap-3"><x-icon name="check" class="mt-1 size-5 text-roof-deep" /> Voyez combien de personnes consultent, appellent ou vous écrivent.</li>
                <li class="flex gap-3"><x-icon name="check" class="mt-1 size-5 text-roof-deep" /> Répondez aux demandes depuis votre boîte de réception.</li>
            </ul>
            <p class="mt-8 text-sm text-muted">Pas encore de compte annonceur ? <a href="{{ route('immo.contact') }}" class="link">Contactez-nous</a>, nous créons votre espace.</p>
        @else
            <p class="eyebrow">Bon retour</p>
            <h1 class="mt-3 text-4xl font-semibold leading-tight">Connectez-vous à votre compte Pratik.</h1>
            <p class="mt-5 max-w-md text-ink/75">Administrateurs, annonceurs et membres utilisent le même formulaire : vous êtes dirigé automatiquement vers votre espace.</p>
        @endif
    </div>

    <div class="card mx-auto w-full max-w-md p-6 sm:p-8">
        <h2 class="text-2xl font-semibold">Connexion</h2>
        @if (session('status'))
            <p class="mt-4 rounded-md bg-garde-wash px-3 py-2 text-sm text-garde">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ $espace === 'immo' ? url('/immobilier/connexion') : url('/connexion') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="label">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input">
                @error('email')<p class="input-error">{{ $message }}</p>@enderror
            </div>
            <div x-data="{ voir: false }">
                <div class="flex items-center justify-between">
                    <label for="password" class="label">Mot de passe</label>
                    <a href="{{ route('password.request') }}" class="mb-1.5 text-xs text-muted hover:text-ink">Mot de passe oublié ?</a>
                </div>
                <div class="relative">
                    <input id="password" name="password" :type="voir ? 'text' : 'password'" required autocomplete="current-password" class="input pr-10">
                    <button type="button" @click="voir = !voir" class="absolute inset-y-0 right-0 px-3 text-muted hover:text-ink" aria-label="Afficher le mot de passe"><x-icon name="eye" class="size-4" /></button>
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="rounded border-line text-ink focus:ring-amber"> Rester connecté</label>
            <button class="{{ $espace === 'immo' ? 'btn-roof' : 'btn-ink' }} w-full py-3">Se connecter</button>
        </form>
        @if ($espace !== 'immo')
            <p class="mt-6 text-center text-sm text-muted">Pas encore inscrit ? <a href="{{ route('register') }}" class="link text-ink">Créer un compte</a></p>
        @endif
    </div>
</div>
@endsection
