@extends('layouts.admin')
@section('title', 'Annonceurs')

@section('content')
<div class="grid gap-6 xl:grid-cols-3">
    <form method="POST" action="{{ route('admin.annonceurs.store') }}" class="card h-fit space-y-4 p-5" x-data="annonceurForm(@js(old('entreprise_id')))">
        @csrf
        <h2 class="font-sans text-base font-semibold">Nouveau compte annonceur</h2>
        <div><label class="label">Nom du contact</label><input name="name" value="{{ old('name') }}" class="input" required></div>
        <div><label class="label">E-mail de connexion</label><input type="email" name="email" value="{{ old('email') }}" class="input" required></div>
        <div><label class="label">Mot de passe provisoire</label><input name="password" value="{{ old('password') }}" class="input" required minlength="8"></div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Téléphone</label><input name="agent_tel" value="{{ old('agent_tel') }}" class="input"></div>
            <div><label class="label">Mobile</label><input name="agent_cel" value="{{ old('agent_cel') }}" class="input"></div>
        </div>
        <div><label class="label">Activité</label>
            <select name="categorie" class="input"><option value="Immobilier">Immobilier</option><option value="Restaurant" @selected(old('categorie') === 'Restaurant')>Restauration</option></select>
        </div>

        <fieldset class="rounded-lg border border-line p-3">
            <legend class="px-1 text-sm font-semibold">Entreprise</legend>
            <div class="mb-2 flex gap-4 text-sm">
                <label class="flex items-center gap-1.5"><input type="radio" x-model="mode" value="existante"> Fiche existante</label>
                <label class="flex items-center gap-1.5"><input type="radio" x-model="mode" value="nouvelle"> Nouvelle fiche</label>
            </div>
            <div x-show="mode === 'existante'" class="relative">
                <input type="hidden" name="entreprise_id" :value="mode === 'existante' && choix ? choix.id : ''">
                <div x-show="choix" class="flex items-center gap-2"><span class="tag" x-text="choix?.titre"></span><button type="button" class="text-xs text-danger" @click="choix = null">changer</button></div>
                <input x-show="!choix" type="text" x-model="q" @input.debounce.300ms="chercher()" class="input" placeholder="Rechercher dans l'annuaire…">
                <ul x-show="resultats.length && !choix" class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md border border-line bg-white shadow-lg">
                    <template x-for="r in resultats" :key="r.id"><li><button type="button" class="w-full px-3 py-2 text-left text-sm hover:bg-sand" @click="choix = r" x-text="r.titre + (r.commune ? ' — ' + r.commune : '')"></button></li></template>
                </ul>
            </div>
            <div x-show="mode === 'nouvelle'" x-cloak class="space-y-2">
                <input name="nouvelle_entreprise" :disabled="mode !== 'nouvelle'" value="{{ old('nouvelle_entreprise') }}" class="input" placeholder="Nom de l'agence / du promoteur">
                <select name="commune_id" :disabled="mode !== 'nouvelle'" class="input"><option value="">Commune</option>@foreach ($communes as $c)<option value="{{ $c->ID }}">{{ $c->DESIGNATION }}</option>@endforeach</select>
                <p class="text-xs text-muted">Une fiche annuaire « client » sera créée ; complétez-la ensuite (logo, adresse…).</p>
            </div>
        </fieldset>
        <button class="btn-ink w-full">Créer le compte</button>
    </form>

    <div class="xl:col-span-2">
        <form method="GET" class="mb-4 flex gap-2"><input name="q" value="{{ $recherche }}" class="input w-72" placeholder="Nom ou e-mail"><button class="btn-line"><x-icon name="search" class="size-4" /></button></form>
        <div class="card overflow-x-auto">
            <table class="table-admin">
                <thead><tr><th></th><th>Annonceur</th><th>Entreprise</th><th class="text-right">Biens</th><th>Compte</th><th></th></tr></thead>
                <tbody>
                    @forelse ($annonceurs as $u)
                        <tr x-data="{ mdp: false }">
                            <td><x-logo-tile :src="$u->entreprise?->annuaire?->logo_url" :name="$u->entreprise?->TITRE ?? $u->name" size="size-9" /></td>
                            <td><p class="font-medium">{{ $u->name }}</p><p class="text-xs text-muted">{{ $u->email }} · {{ $u->CATEGORIE }}</p></td>
                            <td class="text-xs">@if ($u->entreprise)<a href="{{ route('admin.entreprises.edit', $u->entreprise->ID) }}" class="link">{{ $u->entreprise->TITRE }}</a>@else<span class="text-danger">non rattaché</span>@endif</td>
                            <td class="text-right text-xs tabular-nums">{{ $publies[$u->id] ?? 0 }} / {{ $u->biens_count }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.annonceurs.activation', $u->id) }}">@csrf @method('PATCH')
                                    <button class="{{ $u->estActif() ? 'tag-garde' : 'tag text-danger' }}" title="Cliquer pour {{ $u->estActif() ? 'désactiver' : 'activer' }}">{{ $u->estActif() ? 'Actif' : 'Désactivé' }}</button>
                                </form>
                            </td>
                            <td class="text-right">
                                <button type="button" class="btn-line btn-sm" @click="mdp = !mdp"><x-icon name="key" class="size-3.5" /></button>
                                <form x-show="mdp" x-cloak method="POST" action="{{ route('admin.annonceurs.password', $u->id) }}" class="mt-2 flex gap-1">@csrf @method('PUT')
                                    <input name="password" minlength="8" required class="input w-40 py-1.5" placeholder="Nouveau mot de passe"><button class="btn-ink btn-sm">OK</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty icon="briefcase" title="Aucun annonceur" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $annonceurs->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('annonceurForm', (ancien) => ({
            mode: @js(old('nouvelle_entreprise') ? 'nouvelle' : 'existante'),
            choix: ancien ? { id: ancien, titre: 'Entreprise n° ' + ancien } : null,
            q: '', resultats: [],
            async chercher() {
                if (this.q.length < 2) { this.resultats = []; return; }
                const r = await fetch(@js(route('admin.entreprises.lookup')) + '?q=' + encodeURIComponent(this.q), { headers: { Accept: 'application/json' } });
                this.resultats = r.ok ? await r.json() : [];
            },
        }));
    });
</script>
@endpush
@endsection
