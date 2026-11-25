@php
    $immoNav = [
        ['label' => 'Accueil', 'route' => 'immo.home', 'match' => ['immobilier']],
        ['label' => 'Acheter', 'route' => 'immo.achats', 'match' => ['immobilier/achats*', 'immobilier/biens-en-vente*']],
        ['label' => 'Louer', 'route' => 'immo.locations', 'match' => ['immobilier/locations*', 'immobilier/biens-en-location*']],
        ['label' => 'Agences', 'route' => 'immo.agences', 'match' => ['immobilier/agence*']],
        ['label' => 'Promoteurs', 'route' => 'immo.promoteurs', 'match' => ['immobilier/promoteur*', 'immobilier-promoteur*']],
        ['label' => 'Aménageurs fonciers', 'route' => 'immo.amenageurs', 'match' => ['immobilier/amenageurs*']],
        ['label' => 'Immobilier de luxe', 'route' => 'immo.luxe', 'match' => ['immobilier/immobilier-de-luxe*']],
        ['label' => 'Contact', 'route' => 'immo.contact', 'match' => ['immobilier/contact*']],
    ];
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-40 bg-white shadow-[0_1px_0_var(--color-line)]">
    <div class="hidden bg-ink text-[13px] text-white/70 md:block">
        <div class="container-page flex h-9 items-center justify-between">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 hover:text-white">
                <x-icon name="arrow-left" class="size-3.5" /> Retour sur pratik-ci.com
            </a>
            <div class="flex items-center gap-5">
                <a href="{{ tel_link(config('pratik.contact.phone_raw')) }}" class="inline-flex items-center gap-1.5 hover:text-white"><x-icon name="phone" class="size-3.5" /> {{ config('pratik.contact.phone') }}</a>
                @auth
                    @if (auth()->user()->estPro() || auth()->user()->estAdmin())
                        <a href="{{ route('immo.pro.dashboard') }}" class="font-medium text-roof hover:text-white">Mon espace annonceur</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="hover:text-white">Se déconnecter</button></form>
                @else
                    <a href="{{ route('immo.login') }}" class="hover:text-white">Espace annonceur</a>
                @endauth
            </div>
        </div>
    </div>

    <div class="container-page flex h-[68px] items-center gap-6">
        <a href="{{ route('immo.home') }}" class="shrink-0" aria-label="Pratik Immobilier, accueil">
            <img src="{{ asset('brand/pratik-immobilier.jpg') }}" alt="Pratik Immobilier" class="h-12 w-auto mix-blend-multiply" width="96" height="48">
        </a>

        <nav class="hidden flex-1 items-center justify-center gap-0.5 xl:flex" aria-label="Menu immobilier">
            @foreach ($immoNav as $item)
                <a href="{{ route($item['route']) }}" @class([
                    'relative px-3 py-2 text-sm font-medium',
                    'text-ink after:absolute after:inset-x-3 after:-bottom-[15px] after:h-[3px] after:rounded-full after:bg-roof' => request()->is(...$item['match']),
                    'text-ink/65 hover:text-ink' => ! request()->is(...$item['match']),
                ])>{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <a href="{{ route('immo.pro.biens.create') }}" class="btn-roof ml-auto hidden xl:inline-flex">
            <x-icon name="plus" class="size-4" /> Déposer une annonce
        </a>

        <button type="button" class="ml-auto rounded-md p-2 xl:hidden" @click="open = !open" :aria-expanded="open" aria-label="Menu">
            <x-icon name="menu" x-show="!open" />
            <x-icon name="x" x-show="open" x-cloak />
        </button>
    </div>

    <div x-show="open" x-collapse x-cloak class="border-t border-line xl:hidden">
        <nav class="container-page grid gap-1 py-3">
            @foreach ($immoNav as $item)
                <a href="{{ route($item['route']) }}" class="rounded-md px-3 py-2.5 font-medium hover:bg-roof/15">{{ $item['label'] }}</a>
            @endforeach
            <div class="mt-2 grid grid-cols-2 gap-2 border-t border-line pt-3">
                <a href="{{ route('immo.pro.dashboard') }}" class="btn-line">Espace annonceur</a>
                <a href="{{ route('immo.pro.biens.create') }}" class="btn-roof">Déposer une annonce</a>
            </div>
            <a href="{{ route('home') }}" class="mt-1 px-3 py-2 text-sm text-muted">← Retour sur pratik-ci.com</a>
        </nav>
    </div>
</header>
