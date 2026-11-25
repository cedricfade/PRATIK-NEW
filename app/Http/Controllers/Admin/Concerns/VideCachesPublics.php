<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Support\Facades\Cache;

/** Vide les blocs mis en cache côté site public après une modification dans l'admin. */
trait VideCachesPublics
{
    protected function viderCacheGardes(): void
    {
        $jour = now()->toDateString();

        Cache::forget('home:gardes:'.$jour);
        Cache::forget('periode-garde-courante:'.$jour);
    }

    protected function viderCacheAccueil(): void
    {
        foreach (['home:partenaires', 'home:biens', 'home:chiffres', 'home:unes:'.now()->toDateString()] as $cle) {
            Cache::forget($cle);
        }

        $this->viderCacheImmobilier();
    }

    /** Accueil Pratik Immobilier + listes agences/promoteurs/aménageurs. */
    protected function viderCacheImmobilier(): void
    {
        foreach (['immo:home:une', 'immo:home:ventes', 'immo:home:locations', 'immo:home:carte', 'immo:home:compteurs', 'immo:categories', 'immo:types', 'home:biens'] as $cle) {
            Cache::forget($cle);
        }

        \App\Http\Controllers\Immobilier\Support\Annonceurs::oublier();
    }
}
