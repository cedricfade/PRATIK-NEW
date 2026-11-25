{{-- Navigation de l'espace annonceur --}}
@php
    $compte = auth()->user()->loadMissing('entreprise:ID,TITRE');
    $liens = [
        ['Tableau de bord', 'immo.pro.dashboard', 'grid', 'immo.pro.dashboard'],
        ['Mes annonces', 'immo.pro.biens.index', 'home', 'immo.pro.biens.*'],
        ['Messages', 'immo.pro.messages.index', 'inbox', 'immo.pro.messages.*'],
        ['Statistiques', 'immo.pro.statistiques', 'chart', 'immo.pro.statistiques'],
        ['Mon profil', 'immo.pro.profil', 'user', 'immo.pro.profil*'],
    ];
@endphp
<div class="border-b border-line bg-white">
    <div class="container-page flex flex-wrap items-center justify-between gap-3 pt-6">
        <div>
            <p class="eyebrow !text-roof-deep">Espace annonceur</p>
            <p class="mt-0.5 font-serif text-2xl font-semibold">{{ $compte->entreprise?->TITRE ?? $compte->name }}</p>
        </div>
        <a href="{{ route('immo.pro.biens.create') }}" class="btn-roof xl:hidden"><x-icon name="plus" class="size-4" /> Déposer une annonce</a>
    </div>
    <nav class="container-page -mb-px mt-4 flex gap-1 overflow-x-auto text-sm" aria-label="Espace annonceur">
        @foreach ($liens as [$label, $route, $icone, $motif])
            <a href="{{ route($route) }}" @class([
                'inline-flex shrink-0 items-center gap-2 border-b-[3px] px-3 py-3 font-medium',
                'border-roof text-ink' => request()->routeIs($motif),
                'border-transparent text-ink/60 hover:text-ink' => ! request()->routeIs($motif),
            ])><x-icon :name="$icone" class="size-4" /> {{ $label }}</a>
        @endforeach
    </nav>
</div>
