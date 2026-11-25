<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Médias clients (héritage de l'ancien site)
    |--------------------------------------------------------------------------
    |
    | Les logos, photos de biens, unes de journaux… sont stockés dans un dossier
    | "images" avec des sous-dossiers fixes (Logos, ImagesImmobiliers, …).
    | On conserve exactement la même arborescence que l'ancien site pour que
    | toutes les images existantes réapparaissent sans migration de fichiers.
    |
    | - path         : dossier physique (par défaut public/images)
    | - url          : URL publique correspondante
    | - fallback_url : si le fichier est absent en local, on le sert depuis
    |                  l'ancien site (pratique en développement / pendant la bascule)
    |
    */

    'media' => [
        'path' => env('PRATIK_MEDIA_PATH') ?: public_path('images'),
        'url' => rtrim(env('PRATIK_MEDIA_URL', '/images'), '/'),
        'fallback_url' => rtrim((string) env('PRATIK_MEDIA_FALLBACK_URL', ''), '/'),

        'folders' => [
            'logos' => 'Logos',
            'immobilier' => 'ImagesImmobiliers',
            'news' => 'ImagesNews',
            'news_sources' => 'LogosNews',
            'galerie' => 'Galerie',
            'bonsplans' => 'BonsPlans',
            'menus' => 'Menus',
            'pubs' => 'ImagesPubs',
            'papiers' => 'PapierAdministratif',
            'heros' => 'Heros',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Identifiants métier de la base historique
    |--------------------------------------------------------------------------
    */

    // type_entreprise.ID
    'types' => [
        'annuaire' => 1,
        'news' => 2,
        'immobilier' => 3,
        'bonsplans' => 4,
        'pharmacie' => 5,
    ],

    // roles.id
    'roles' => [
        'super_admin' => 1,
        'admin' => 2,
        'user' => 3,
        'restauration' => 4,
        'immobilier' => 5,
        'visiteur' => 7,
    ],

    // sous_categorie.ID des pharmacies dans l'annuaire
    'pharmacie_sous_categorie' => 409,

    // communes considérées comme "Abidjan" pour les pharmacies de garde
    'communes_abidjan' => [2, 3, 4, 5, 6, 8, 9, 11, 12, 13, 14, 15, 16, 17],

    // type_immobilier.ID
    'immo_types' => [
        'agence_location' => 1,
        'agence_vente' => 2,
        'promoteur' => 3,
        'amenageur' => 4,
    ],

    // news.CATEGORIE_ID
    'news_categories' => [
        'videos' => 1,
        'web' => 2,
        'titrologie' => 3,
    ],

    'contact' => [
        'phone' => '+225 27 22 48 44 33',
        'phone_raw' => '+2252722484433',
        'email' => 'infopratik2@gmail.com',
        'address' => 'Abidjan, Côte d\'Ivoire',
        'facebook' => 'https://www.facebook.com/profile.php?id=61554337137568',
        'instagram' => 'https://www.instagram.com/pratik_cotedivoire/',
        'linkedin' => 'https://www.linkedin.com/company/pratik-ci/',
        'x' => 'https://x.com/pratikcontact',
        'tiktok' => 'https://www.tiktok.com/@pratik.ci',
        'youtube' => 'https://www.youtube.com/channel/UCaC7G8yGfdvfTIE4aWLBuqQ',
    ],

    'apps' => [
        'android' => 'https://play.google.com/store/apps/details?id=com.gmail.infopratik2.pratik',
        'ios' => 'https://apps.apple.com/lu/app/pratik-ci/id6478446607',
    ],

    'google_maps_key' => env('GOOGLE_MAPS_API_KEY'),

    'analytics_id' => env('GA_MEASUREMENT_ID'),
    'adsense_client' => env('ADSENSE_CLIENT'),

    'aerodb' => [
        'url' => 'https://prod.api.market/api/v1/aedbx/aerodatabox',
        'key' => env('AERODB_API_KEY'),
        'airport' => 'ABJ',
    ],
];
