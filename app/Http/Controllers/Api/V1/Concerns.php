<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** Outils partagés par les contrôleurs de l'API. */
trait Concerns
{
    protected function parPage(Request $request, int $defaut = 20): int
    {
        return max(1, min(50, (int) $request->integer('per_page', $defaut)));
    }

    /** Résout ?commune= (lien ou ID) ; 404 si inconnue. */
    protected function commune(Request $request): ?Commune
    {
        $valeur = trim((string) $request->query('commune', ''));

        if ($valeur === '') {
            return null;
        }

        $commune = Cache::remember('api:commune:'.md5($valeur), 86400, fn () => Commune::query()
            ->when(ctype_digit($valeur), fn ($q) => $q->where('ID', (int) $valeur), fn ($q) => $q->where('LIEN', $valeur))
            ->first(['ID', 'DESIGNATION', 'LIEN']));

        abort_if(! $commune, 404, 'Commune introuvable.');

        return $commune;
    }
}
