<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\PapierAdministratif;
use Illuminate\View\View;

/** Documents nationaux + liens propres à la ville choisie (mairie, etc.). */
class PapierController extends Controller
{
    public function __invoke(?string $lien = null): View
    {
        $commune = $lien ? Commune::where('LIEN', $lien)->firstOrFail() : null;

        $itemsCommune = $commune
            ? PapierAdministratif::with('pieces')
                ->where('ACTIVE', 1)
                ->where('COMMUNE_ID', $commune->ID)
                ->orderBy('ORDRE')
                ->orderBy('TITRE')
                ->get()
            : collect();

        $items = PapierAdministratif::with('pieces')
            ->where('ACTIVE', 1)
            ->whereNull('COMMUNE_ID')
            ->orderBy('ORDRE')
            ->orderByDesc('DATE_LOG')
            ->get();

        // Seules les villes ayant au moins un lien apparaissent dans le choix.
        $communes = Commune::whereIn('ID', PapierAdministratif::where('ACTIVE', 1)->whereNotNull('COMMUNE_ID')->select('COMMUNE_ID'))
            ->orderBy('DESIGNATION')
            ->get(['ID', 'DESIGNATION', 'LIEN']);

        return view('site.papiers', compact('items', 'itemsCommune', 'communes', 'commune'));
    }
}
