@extends('layouts.admin')
@section('title', 'Tableau de bord')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-admin.stat label="Pharmacies de garde aujourd'hui" :value="$kpi['gardes']" icon="cross" tone="garde" :href="route('admin.gardes.index')" :hint="$periode ? 'Semaine '.$periode->libelle : 'Aucune période en cours'" />
    <x-admin.stat label="Biens en attente de validation" :value="$kpi['biens_attente']" icon="home" :tone="$kpi['biens_attente'] ? 'roof' : 'ink'" :href="route('admin.biens.index', ['statut' => 'attente'])" :hint="number_format($kpi['biens_publies'], 0, ',', ' ').' publiés'" />
    <x-admin.stat label="Unes publiées aujourd'hui" :value="$kpi['unes']" icon="newspaper" tone="amber" :href="route('admin.titrologie.index')" />
    <x-admin.stat label="Messages reçus (7 jours)" :value="$kpi['messages_semaine']" icon="inbox" :href="route('admin.messages.index')" :hint="$kpi['messages_entreprises'].' messages annonceurs non lus'" />
    <x-admin.stat label="Entreprises dans l'annuaire" :value="$kpi['entreprises']" icon="building" :href="route('admin.entreprises.index')" />
    <x-admin.stat label="Pros VIP (TOP 12)" :value="$kpi['clients']" icon="crown" tone="amber" :href="route('admin.entreprises.index', ['vip' => 1])" />
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <section class="card p-5 xl:col-span-1">
        <h2 class="font-sans text-base font-semibold">Garde de la semaine</h2>
        @if ($periode)
            <p class="mt-1 text-sm text-muted">En cours : {{ $periode->libelle }}</p>
        @else
            <p class="mt-1 text-sm text-danger">Aucune période ne couvre aujourd'hui.</p>
        @endif
        @if ($prochaine)
            <p class="mt-3 text-sm">Prochaine : <strong>{{ $prochaine->libelle }}</strong></p>
            <p class="text-sm {{ $prochaineCount ? 'text-garde' : 'text-amber-deep' }}">{{ $prochaineCount }} pharmacie(s) déjà affectée(s)</p>
        @endif
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('admin.gardes.index', $prochaine ? ['periode' => $prochaine->ID] : []) }}" class="btn-garde btn-sm"><x-icon name="cross" class="size-3.5" /> Gérer les gardes</a>
            <a href="{{ route('admin.periodes.index') }}" class="btn-line btn-sm"><x-icon name="calendar" class="size-3.5" /> Nouvelle période</a>
        </div>
    </section>

    <section class="card p-5">
        <div class="flex items-center justify-between">
            <h2 class="font-sans text-base font-semibold">Biens à valider</h2>
            <a href="{{ route('admin.biens.index', ['statut' => 'attente']) }}" class="text-sm text-muted hover:text-ink">Tout voir</a>
        </div>
        <ul class="mt-3 divide-y divide-line text-sm">
            @forelse ($derniersBiens as $b)
                <li class="flex items-center justify-between gap-3 py-2">
                    <a href="{{ route('admin.biens.edit', $b->ID) }}" class="truncate hover:underline">{{ $b->entreprise?->TITRE ?? 'Bien #'.$b->ID }}</a>
                    <span class="shrink-0 text-xs text-muted">{{ $b->DATE_LOG }}</span>
                </li>
            @empty
                <li class="py-3 text-muted">Rien en attente.</li>
            @endforelse
        </ul>
    </section>

    <section class="card p-5">
        <div class="flex items-center justify-between">
            <h2 class="font-sans text-base font-semibold">Derniers messages</h2>
            <a href="{{ route('admin.messages.index') }}" class="text-sm text-muted hover:text-ink">Tout voir</a>
        </div>
        <ul class="mt-3 divide-y divide-line text-sm">
            @forelse ($derniersMessages as $m)
                <li class="py-2">
                    <p class="truncate font-medium">{{ $m->nom ?: 'Anonyme' }} <span class="font-normal text-muted">· {{ $m->categorie }}</span></p>
                    <p class="truncate text-muted">{{ $m->objet }}</p>
                </li>
            @empty
                <li class="py-3 text-muted">Aucun message.</li>
            @endforelse
        </ul>
    </section>
</div>

<section class="mt-6 card p-5">
    <h2 class="font-sans text-base font-semibold">Raccourcis</h2>
    <div class="mt-3 flex flex-wrap gap-2">
        <a href="{{ route('admin.titrologie.index') }}" class="btn-line btn-sm"><x-icon name="upload" class="size-3.5" /> Publier les unes du jour</a>
        <a href="{{ route('admin.entreprises.create') }}" class="btn-line btn-sm"><x-icon name="plus" class="size-3.5" /> Nouvelle entreprise</a>
        <a href="{{ route('admin.news-web.index') }}" class="btn-line btn-sm"><x-icon name="globe" class="size-3.5" /> Ajouter une news web</a>
        <a href="{{ route('admin.annonceurs.index') }}" class="btn-line btn-sm"><x-icon name="briefcase" class="size-3.5" /> Créer un annonceur</a>
        <a href="{{ route('admin.papiers.index') }}" class="btn-line btn-sm"><x-icon name="file-text" class="size-3.5" /> Papiers administratifs</a>
    </div>
</section>
@endsection
