<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.head')
</head>
<body class="flex min-h-screen flex-col">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-2 focus:rounded focus:bg-white focus:px-3 focus:py-2">Aller au contenu</a>

    @include('partials.site.header')

    <main id="contenu" class="flex-1">
        @include('partials.flash')
        @yield('content')
    </main>

    @include('partials.site.footer')
    @stack('scripts')
</body>
</html>
