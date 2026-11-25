<!DOCTYPE html>
<html lang="fr">
<head>
    @php $siteName = 'Pratik Immobilier'; @endphp
    @include('partials.head')
</head>
<body class="flex min-h-screen flex-col bg-[#faf9f6]">
    @include('partials.immo.header')

    <main id="contenu" class="flex-1">
        @include('partials.flash')
        @yield('content')
    </main>

    @include('partials.immo.footer')
    @stack('scripts')
</body>
</html>
