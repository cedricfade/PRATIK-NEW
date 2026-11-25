@extends('layouts.admin')
@section('title', 'Utilisateurs')

@section('content')
<div class="grid gap-6 xl:grid-cols-4">
    <div class="space-y-6">
        <form method="GET" class="card space-y-3 p-5">
            <h2 class="font-sans text-sm font-semibold">Filtrer</h2>
            <input name="q" value="{{ $recherche }}" class="input" placeholder="Nom ou e-mail">
            <select name="role" class="input"><option value="">Tous les rôles</option>@foreach ($roles as $r)<option value="{{ $r->id }}" @selected(request('role') == $r->id)>{{ $r->name }}</option>@endforeach</select>
            <button class="btn-line w-full"><x-icon name="search" class="size-4" /> Filtrer</button>
        </form>

        @if (auth()->user()->estSuperAdmin())
            <form method="POST" action="{{ route('admin.users.store') }}" class="card space-y-3 p-5">
                @csrf
                <h2 class="font-sans text-sm font-semibold">Nouvel administrateur</h2>
                <input name="name" value="{{ old('name') }}" class="input" placeholder="Nom" required>
                <input type="email" name="email" value="{{ old('email') }}" class="input" placeholder="E-mail" required>
                <input name="password" class="input" placeholder="Mot de passe (10 caractères min.)" minlength="10" required>
                <select name="role_id" class="input"><option value="{{ config('pratik.roles.admin') }}">Administrateur</option><option value="{{ config('pratik.roles.super_admin') }}">Super administrateur</option></select>
                <button class="btn-ink w-full">Créer</button>
            </form>
        @endif
        <p class="text-xs text-muted">Les comptes annonceurs se créent depuis <a href="{{ route('admin.annonceurs.index') }}" class="link">Annonceurs</a>.</p>
    </div>

    <div class="xl:col-span-3">
        <div class="card overflow-x-auto">
            <table class="table-admin">
                <thead><tr><th>Utilisateur</th><th>Rôle & statut</th><th>Entreprise</th><th>Inscrit</th><th></th></tr></thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr x-data="{ mdp: false }">
                            <td><p class="font-medium">{{ $u->name }}</p><p class="text-xs text-muted">{{ $u->email }}</p></td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.update', $u->id) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf @method('PUT')
                                    <select name="role_id" class="input w-40 py-1.5 text-xs">@foreach ($roles as $r)<option value="{{ $r->id }}" @selected($u->role_id === $r->id)>{{ $r->name }}</option>@endforeach</select>
                                    <label class="flex items-center gap-1 text-xs"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked($u->estActif()) class="rounded border-line"> actif</label>
                                    <button class="btn-line btn-sm">OK</button>
                                </form>
                            </td>
                            <td class="text-xs">{{ $u->entreprise?->TITRE }}</td>
                            <td class="text-xs whitespace-nowrap">{{ $u->created_at?->format('d/m/Y') }}</td>
                            <td class="text-right">
                                <button type="button" class="btn-line btn-sm" @click="mdp = !mdp" title="Changer le mot de passe"><x-icon name="key" class="size-3.5" /></button>
                                <form x-show="mdp" x-cloak method="POST" action="{{ route('admin.users.password', $u->id) }}" class="mt-2 flex gap-1">@csrf @method('PUT')
                                    <input name="password" minlength="8" required class="input w-40 py-1.5" placeholder="Nouveau mot de passe"><button class="btn-ink btn-sm">OK</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty icon="users" title="Aucun utilisateur" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>
</div>
@endsection
