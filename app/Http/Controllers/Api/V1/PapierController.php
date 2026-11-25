<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CommuneResource;
use App\Http\Resources\Api\PapierResource;
use App\Models\Commune;
use App\Models\PapierAdministratif;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Démarches nationales + liens propres à une ville (mairie…), comme la page publique. */
class PapierController extends Controller
{
    use Concerns;

    public function index(Request $request): JsonResponse
    {
        $commune = $this->commune($request);

        $requete = fn () => PapierAdministratif::with('pieces:ID,PAPIER_ID,LIBELLE,RANG')->where('ACTIVE', 1)->orderBy('ORDRE')->orderBy('TITRE');

        $nationaux = $requete()->whereNull('COMMUNE_ID')->get();
        $locaux = $commune ? $requete()->where('COMMUNE_ID', $commune->ID)->get() : collect();

        $communes = Commune::whereIn('ID', PapierAdministratif::where('ACTIVE', 1)->whereNotNull('COMMUNE_ID')->select('COMMUNE_ID'))
            ->orderBy('DESIGNATION')
            ->get(['ID', 'DESIGNATION', 'LIEN']);

        return response()->json(['data' => [
            'nationaux' => PapierResource::collection($nationaux),
            'commune' => $commune ? ['id' => $commune->ID, 'designation' => $commune->DESIGNATION, 'lien' => $commune->LIEN] : null,
            'locaux' => PapierResource::collection($locaux),
            'communes_disponibles' => CommuneResource::collection($communes),
        ]]);
    }
}
