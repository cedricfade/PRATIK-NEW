@php
    $siteName = $siteName ?? 'Pratik';
    // @section('x', 'valeur') échappe déjà la valeur : on la décode pour ne pas l'échapper deux fois.
    $decode = fn (string $v) => html_entity_decode(trim($v), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageTitle = $decode($__env->yieldContent('title'));
    $fullTitle = $pageTitle ? $pageTitle.' · '.$siteName : $siteName.' — L\'annuaire pratique de Côte d\'Ivoire';
    $description = $decode($__env->yieldContent('description')) ?: 'Pratik : annuaire des entreprises de Côte d\'Ivoire, pharmacies de garde, actualités, papiers administratifs, horaires des vols et immobilier.';
    $canonical = $decode($__env->yieldContent('canonical')) ?: url()->current();
    $ogImage = $decode($__env->yieldContent('og_image')) ?: asset('brand/pratik.png');
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($description), 160) }}">
<link rel="canonical" href="{{ $canonical }}">
@hasSection('noindex')
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large">
@endif
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="fr_CI">
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($description), 200) }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#14202b">
<link rel="icon" href="{{ asset('brand/favicon.svg') }}" type="image/svg+xml">
<link rel="icon" href="{{ asset('brand/favicon.png') }}" sizes="16x16">
<link rel="preconnect" href="https://pratik-ci.com">
@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('head')
@if (app()->isProduction() && config('pratik.analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('pratik.analytics_id') }}"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ config('pratik.analytics_id') }}');</script>
@endif
@if (app()->isProduction() && config('pratik.adsense_client') && ! request()->is('admin*', 'immobilier/espace-pro*'))
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('pratik.adsense_client') }}" crossorigin="anonymous"></script>
@endif
