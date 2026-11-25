@extends('layouts.immo')

@section('title', 'Tableau de bord annonceur')
@section('noindex', true)

@section('content')
    <x-immo.pro-nav />

    <div class="container-page space-y-8 py-8">
        @if (! auth()->user()->entreprise_id)
            <div class="rounded-lg border border-roof/40 bg-roof/10 px-4 py-3 text-sm">
                Votre compte n'est pas encore rattaché à une fiche société : vos annonces, vues et messages sont bien suivis,
                mais vous n'avez pas de page agence publique avec votre logo.
                <a href="{{ route('immo.contact') }}" class="link text-ink">Contactez-nous</a> pour la créer.
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $tuiles = [
                    ['Annonces en ligne', $biens['publies'], route('immo.pro.biens.index', ['statut' => 'publies']), 'home'],
                    ['En attente de validation', $biens['attente'], route('immo.pro.biens.index', ['statut' => 'attente']), 'clock'],
                    ['Vues de vos annonces', $stats['vues_biens'] ?? 0, route('immo.pro.statistiques'), 'eye'],
                    ['Messages non lus', $nonLus, route('immo.pro.messages.index', ['filtre' => 'non-lus']), 'inbox'],
                ];
            @endphp
            @foreach ($tuiles as [$label, $valeur, $lien, $icone])
                <a href="{{ $lien }}" class="card card-hover p-5">
                    <div class="flex items-center justify-between text-sm text-muted">{{ $label }} <x-icon :name="$icone" class="size-4" /></div>
                    <p class="mt-2 font-serif text-3xl font-semibold">{{ number_format($valeur, 0, ',', ' ') }}</p>
                </a>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
            <section class="card">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h2 class="font-sans text-base font-semibold">Mes dernières annonces</h2>
                    <a href="{{ route('immo.pro.biens.index') }}" class="text-sm text-roof-deep hover:underline">Tout voir</a>
                </div>
                @forelse ($derniers as $bien)
                    <div class="flex items-center gap-4 border-b border-line/70 px-5 py-3 last:border-0">
                        <img src="{{ $bien->image_url }}" alt="" loading="lazy" class="size-14 shrink-0 rounded-md bg-sand object-cover">
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('immo.pro.biens.edit', $bien->ID) }}" class="line-clamp-1 font-medium hover:text-roof-deep">{{ $bien->entreprise?->TITRE }}</a>
                            <p class="text-[13px] text-muted">{{ $bien->prix_affiche }} · {{ number_format((int) $bien->entreprise?->vues_affichees, 0, ',', ' ') }} vues</p>
                        </div>
                        <x-immo.statut :validation="$bien->VALIDATION" class="hidden sm:inline-flex" />
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="text-ink/70">Vous n'avez pas encore publié d'annonce.</p>
                        <a href="{{ route('immo.pro.biens.create') }}" class="btn-roof mt-4">Déposer ma première annonce</a>
                    </div>
                @endforelse
            </section>

            <div class="space-y-6">
                <section class="card p-5">
                    @foreach (['mois' => 'Ce mois-ci ('.now()->locale('fr')->isoFormat('MMMM').')', 'annee' => 'Cette année ('.now()->year.')'] as $periode => $titre)
                        <h2 class="font-sans text-base font-semibold first-letter:uppercase {{ $loop->first ? '' : 'mt-5' }}">{{ $titre }}</h2>
                        <dl class="mt-3 grid grid-cols-3 gap-3 text-center">
                            @foreach (['vues' => 'Consultations', 'appels' => 'Appels', 'emails' => 'E-mails'] as $cle => $label)
                                <div class="rounded-lg bg-sand/70 p-3">
                                    <dd class="font-serif text-2xl font-semibold">{{ number_format($stats[$periode][$cle] ?? 0, 0, ',', ' ') }}</dd>
                                    <dt class="text-xs text-muted">{{ $label }}</dt>
                                </div>
                            @endforeach
                        </dl>
                    @endforeach
                    <a href="{{ route('immo.pro.statistiques') }}" class="link mt-4 inline-block text-sm">Statistiques par période</a>
                </section>

                <section class="card">
                    <div class="flex items-center justify-between border-b border-line px-5 py-4">
                        <h2 class="font-sans text-base font-semibold">Derniers messages</h2>
                        <a href="{{ route('immo.pro.messages.index') }}" class="text-sm text-roof-deep hover:underline">Boîte de réception</a>
                    </div>
                    @forelse ($messages as $m)
                        <a href="{{ route('immo.pro.messages.show', $m->id) }}" class="block border-b border-line/70 px-5 py-3 last:border-0 hover:bg-paper">
                            <p class="flex items-center gap-2 text-sm {{ $m->status == 0 ? 'font-semibold' : '' }}">
                                @if ($m->status == 0)<span class="size-2 shrink-0 rounded-full bg-roof"></span>@endif
                                {{ trim($m->prenom.' '.$m->nom) }}
                                <span class="ml-auto shrink-0 text-xs font-normal text-muted">{{ $m->created_at?->locale('fr')->diffForHumans() }}</span>
                            </p>
                            <p class="mt-0.5 line-clamp-1 text-[13px] text-muted">{{ $m->contactmessage }}</p>
                        </a>
                    @empty
                        <p class="px-5 py-6 text-sm text-muted">Aucun message pour l'instant.</p>
                    @endforelse
                </section>
            </div>
        </div>
    </div>
@endsection
