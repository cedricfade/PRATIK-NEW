<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\VolsController as VolsSite;
use App\Models\Statistique;
use App\Services\FlightService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/v1/vols?aeroport=ABJ&date=AAAA-MM-JJ — départs et arrivées d'un aéroport.
 * Même source et même cache (5 min) que la page « Horaires des vols » du site.
 */
class VolsController extends Controller
{
    public function __invoke(Request $request, FlightService $service): JsonResponse
    {
        $aeroport = strtoupper((string) $request->query('aeroport', 'ABJ'));
        if (! array_key_exists($aeroport, VolsSite::AEROPORTS)) {
            $aeroport = 'ABJ';
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', (string) $request->query('date', now()->toDateString()));
        } catch (\Throwable) {
            $date = now();
        }
        $date = $date->between(now()->subDay()->startOfDay(), now()->addDays(2)->endOfDay()) ? $date : now();

        Statistique::enregistrer(null, Statistique::VOLS);

        $donnees = $service->vols($aeroport, $date->toDateString());

        return response()->json(['data' => [
            'aeroport' => $aeroport,
            'aeroports' => collect(VolsSite::AEROPORTS)->map(fn ($nom, $code) => ['code' => $code, 'nom' => $nom])->values(),
            'date' => $date->toDateString(),
            'departs' => array_map(fn ($v) => FlightService::normaliser($v, 'depart'), $donnees['departures']),
            'arrivees' => array_map(fn ($v) => FlightService::normaliser($v, 'arrivee'), $donnees['arrivals']),
            'indisponible' => (bool) $donnees['erreur'],
        ]]);
    }
}
