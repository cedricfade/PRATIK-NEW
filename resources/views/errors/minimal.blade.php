{{-- Page d'erreur autonome : ne dépend pas de la base de données (utile si elle est indisponible). --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · Pratik</title>
    <link rel="icon" href="/brand/favicon.svg" type="image/svg+xml">
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#fbf8f3;color:#14202b;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;padding:24px}
        main{max-width:520px}
        .code{font:600 14px/1 ui-monospace,monospace;letter-spacing:.14em;color:#a8650e;text-transform:uppercase}
        h1{font-family:Georgia,"Source Serif 4",serif;font-size:34px;line-height:1.15;margin:14px 0 12px}
        p{color:#4b5563;line-height:1.6;margin:0 0 24px}
        a{display:inline-block;background:#14202b;color:#fff;text-decoration:none;padding:11px 18px;border-radius:6px;font-weight:600;font-size:14px;margin-right:8px}
        a.sec{background:#fff;color:#14202b;border:1px solid #e6ded1}
        img{height:30px;margin-bottom:36px}
    </style>
</head>
<body>
<main>
    <img src="/brand/pratik.png" alt="Pratik">
    <p class="code">Erreur @yield('code')</p>
    <h1>@yield('title')</h1>
    <p>@yield('message')</p>
    <a href="/">Retour à l'accueil</a>
    <a href="/pharmacies-de-garde" class="sec">Pharmacies de garde</a>
</main>
</body>
</html>
