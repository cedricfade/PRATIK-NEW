@extends('layouts.site')
@section('title', 'Mon compte')
@section('noindex', true)

@section('content')
<div class="container-page py-10">
    <h1 class="text-3xl font-semibold">Mon compte</h1>
    <p class="mt-1 text-muted">{{ $user->role_libelle }}</p>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('compte.update') }}" class="card space-y-4 p-6">
            @csrf @method('PUT')
            <h2 class="text-lg font-semibold">Mes informations</h2>
            <div><label class="label">Nom</label><input name="name" value="{{ old('name', $user->name) }}" class="input" required>@error('name')<p class="input-error">{{ $message }}</p>@enderror</div>
            <div><label class="label">E-mail</label><input type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required>@error('email')<p class="input-error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Téléphone</label><input name="agent_cel" value="{{ old('agent_cel', $user->agent_cel) }}" class="input"></div>
            <button class="btn-ink">Enregistrer</button>
        </form>

        <form method="POST" action="{{ route('compte.password') }}" class="card space-y-4 p-6">
            @csrf @method('PUT')
            <h2 class="text-lg font-semibold">Mot de passe</h2>
            <div><label class="label">Mot de passe actuel</label><input type="password" name="current_password" class="input" required>@error('current_password')<p class="input-error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Nouveau mot de passe</label><input type="password" name="password" class="input" required>@error('password')<p class="input-error">{{ $message }}</p>@enderror</div>
            <div><label class="label">Confirmation</label><input type="password" name="password_confirmation" class="input" required></div>
            <button class="btn-line">Modifier le mot de passe</button>
        </form>
    </div>
</div>
@endsection
