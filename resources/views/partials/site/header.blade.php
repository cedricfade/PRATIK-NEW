@php
    $nav = [
        ['label' => 'Annuaire', 'route' => 'annuaire.index', 'match' => ['annuaire*', 'details/*', 'recherche*']],
        ['label' => 'Pharmacies de garde', 'route' => 'pharmacies.index', 'match' => ['pharmacies-de-garde*']],
        ['label' => 'News', 'route' => 'news.index', 'match' => ['news*', 'archives-news*']],
        ['label' => 'Papiers administratifs', 'route' => 'papiers.index', 'match' => ['papier-administratif*']],
        ['label' => 'Horaires des vols', 'route' => 'vols.index', 'match' => ['horaires-des-vols*', 'Horaires-Avions*']],
    ];
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/85">
    {{-- Bandeau utilitaire --}}
    <div class="hidden border-b border-line bg-ink text-[13px] text-white/80 md:block">
        <div class="container-page flex h-9 items-center justify-between gap-6">
            <p class="first-letter:uppercase">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
            <div class="flex items-center gap-5">
                @if ($periodeGarde ?? null)
                    <a href="{{ route('pharmacies.index') }}" class="inline-flex items-center gap-1.5 hover:text-white">
                        <span class="size-1.5 rounded-full bg-emerald-400"></span>
                        Gardes {{ $periodeGarde->libelle }}
                    </a>
                @endif
                <a href="{{ tel_link(config('pratik.contact.phone_raw')) }}" class="inline-flex items-center gap-1.5 hover:text-white">
                    <x-icon name="phone" class="size-3.5" /> {{ config('pratik.contact.phone') }}
                </a>
            </div>
        </div>
    </div>

    <div class="container-page flex h-16 items-center gap-6">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="Pratik, accueil">
            <img src="{{ asset('brand/pratik.png') }}" alt="Pratik" class="h-8 w-auto" width="84" height="32">
        </a>

        <nav class="hidden flex-1 items-center gap-1 lg:flex" aria-label="Navigation principale">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" @class([
                    'rounded-md px-3 py-2 text-sm font-medium',
                    'text-ink bg-sand' => request()->is(...$item['match']),
                    'text-ink/70 hover:text-ink' => ! request()->is(...$item['match']),
                ])>{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('immo.home') }}" target="_blank" rel="noopener" class="ml-1 inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold text-roof-deep hover:bg-roof/15">
                Immobilier <x-icon name="external" class="size-3.5" />
            </a>
        </nav>

        <div class="ml-auto hidden items-center gap-2 lg:flex">
            @auth
                <a href="{{ auth()->user()->estAdmin() ? route('admin.dashboard') : (auth()->user()->estPro() ? route('immo.pro.dashboard') : route('compte')) }}" class="btn-ghost">
                    <x-icon name="user" class="size-4" /> {{ \Illuminate\Support\Str::words(auth()->user()->name, 2, '') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Se connecter</a>
            @endauth
            <a href="{{ route('souscription') }}" class="btn-amber">Inscrire mon entreprise</a>
        </div>

        <button type="button" class="ml-auto rounded-md p-2 lg:hidden" @click="open = !open" :aria-expanded="open" aria-label="Menu">
            <x-icon name="menu" x-show="!open" />
            <x-icon name="x" x-show="open" x-cloak />
        </button>
    </div>

    {{-- Menu mobile --}}
    <div x-show="open" x-collapse x-cloak class="border-t border-line bg-white lg:hidden">
        <nav class="container-page grid gap-1 py-3">
            <a href="{{ route('home') }}" class="rounded-md px-3 py-2.5 font-medium hover:bg-sand">Accueil</a>
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" class="rounded-md px-3 py-2.5 font-medium hover:bg-sand">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('immo.home') }}" target="_blank" rel="noopener" class="flex items-center justify-between rounded-md px-3 py-2.5 font-semibold text-roof-deep hover:bg-roof/15">
                Pratik Immobilier <x-icon name="external" class="size-4" />
            </a>
            <div class="mt-2 grid grid-cols-2 gap-2 border-t border-line pt-3">
                @auth
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-line w-full">Se déconnecter</button></form>
                @else
                    <a href="{{ route('login') }}" class="btn-line">Se connecter</a>
                @endauth
                <a href="{{ route('souscription') }}" class="btn-amber">Inscrire mon entreprise</a>
            </div>
        </nav>
    </div>
</header>
