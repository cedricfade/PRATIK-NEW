<!DOCTYPE html>
<html lang="fr">
<head>
    @php $siteName = 'Pratik Admin'; @endphp
    @section('noindex', true)
    @include('partials.head')
</head>
<body class="bg-[#f6f4ef]" x-data="{ sidebar: false }">
    @php
        $menu = [
            'Pharmacies' => [
                ['Gardes en cours', 'admin.gardes.index', 'cross'],
                ['Périodes de garde', 'admin.periodes.index', 'calendar'],
                ['Liste des pharmacies', 'admin.pharmacies.index', 'list'],
            ],
            'Annuaire' => [
                ['Entreprises', 'admin.entreprises.index', 'building'],
                ['Catégories', 'admin.categories.index', 'layers'],
                ['Sous-catégories', 'admin.sous-categories.index', 'tag'],
                ['Communes', 'admin.communes.index', 'map-pin'],
            ],
            'Immobilier' => [
                ['Biens immobiliers', 'admin.biens.index', 'home'],
                ['Annonceurs', 'admin.annonceurs.index', 'briefcase'],
                ['Catégories & types', 'admin.immo-referentiels.index', 'layers'],
            ],
            'News' => [
                ['Titrologie (unes)', 'admin.titrologie.index', 'newspaper'],
                ['News web', 'admin.news-web.index', 'globe'],
                ['Vidéos', 'admin.videos.index', 'play'],
                ['Sources', 'admin.sources.index', 'list'],
            ],
            'Contenus' => [
                ['Apparence (accueil)', 'admin.apparence.index', 'image'],
                ['Vues (compteurs)', 'admin.vues.index', 'eye'],
                ['Papiers administratifs', 'admin.papiers.index', 'file-text'],
            ],
            'Relation clients' => [
                ['Messages reçus', 'admin.messages.index', 'inbox'],
                ['Utilisateurs', 'admin.users.index', 'users'],
            ],
        ];
    @endphp

    {{-- Barre latérale --}}
    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-ink text-white/70 transition-transform">
        <div class="flex h-16 items-center gap-2 border-b border-white/10 px-5">
            <a href="{{ route('admin.dashboard') }}" class="font-serif text-xl text-white">Pratik<span class="text-amber">.</span></a>
            <span class="rounded bg-white/10 px-1.5 py-0.5 text-[10px] font-semibold tracking-wider text-white/70 uppercase">Admin</span>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 py-4 text-sm">
            <a href="{{ route('admin.dashboard') }}" @class(['flex items-center gap-2.5 rounded-md px-3 py-2', 'bg-white/10 text-white' => request()->routeIs('admin.dashboard'), 'hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.dashboard')])>
                <x-icon name="grid" class="size-4" /> Tableau de bord
            </a>
            @foreach ($menu as $section => $items)
                <p class="mt-5 mb-1.5 px-3 text-[11px] font-semibold tracking-wider text-white/40 uppercase">{{ $section }}</p>
                @foreach ($items as [$label, $route, $icon])
                    @if (Route::has($route))
                        @php($actif = request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.').'.*'))
                        <a href="{{ route($route) }}" @class(['flex items-center gap-2.5 rounded-md px-3 py-2', 'bg-white/10 text-white' => $actif, 'hover:bg-white/5 hover:text-white' => ! $actif])>
                            <x-icon :name="$icon" class="size-4" /> {{ $label }}
                        </a>
                    @endif
                @endforeach
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-3 text-sm">
            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2.5 rounded-md px-3 py-2 hover:bg-white/5 hover:text-white"><x-icon name="external" class="size-4" /> Voir le site</a>
        </div>
    </aside>
    <div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-ink/40 lg:hidden"></div>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-line bg-white px-4 sm:px-6">
            <button class="rounded-md p-2 lg:hidden" @click="sidebar = true" aria-label="Menu"><x-icon name="menu" /></button>
            <div class="min-w-0 flex-1">
                <h1 class="truncate font-serif text-lg font-semibold">@yield('title', 'Tableau de bord')</h1>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <span class="hidden text-muted sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="btn-line btn-sm"><x-icon name="log-out" class="size-3.5" /> Sortir</button>
                </form>
            </div>
        </header>

        @include('partials.flash')

        @if ($errors->any())
            <div class="px-4 pt-4 sm:px-6">
                <div class="rounded-lg border border-danger/30 bg-red-50 px-4 py-3 text-sm text-danger">
                    <p class="font-semibold">Le formulaire contient des erreurs :</p>
                    <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            </div>
        @endif

        <main class="p-4 sm:p-6">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
