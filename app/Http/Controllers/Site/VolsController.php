<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Statistique;
use App\Services\FlightService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolsController extends Controller
{
    public const AEROPORTS = [
        'ABJ' => 'Abidjan — Félix Houphouët-Boigny',
        'CDG' => 'Paris — Charles de Gaulle',
        'ORY' => 'Paris — Orly',
        'BRU' => 'Bruxelles',
        'CMN' => 'Casablanca',
        'DSS' => 'Dakar — Blaise Diagne',
        'ACC' => 'Accra',
        'LOS' => 'Lagos',
        'ADD' => 'Addis-Abeba',
        'IST' => 'Istanbul',
        'DXB' => 'Dubaï',
        'JFK' => 'New York — JFK',
    ];

    public function __invoke(Request $request, FlightService $service): View
    {
        $aeroport = strtoupper((string) $request->query('aeroport', config('pratik.aerodb.airport', 'ABJ')));
        if (! array_key_exists($aeroport, self::AEROPORTS)) {
            $aeroport = 'ABJ';
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', (string) $request->query('date', now()->toDateString()));
        } catch (\Throwable) {
            $date = now();
        }
        // L'API ne couvre qu'une fenêtre proche : on borne à J-1 / J+2.
        $date = $date->between(now()->subDay()->startOfDay(), now()->addDays(2)->endOfDay()) ? $date : now();

        Statistique::enregistrer(null, Statistique::VOLS);

        $donnees = $service->vols($aeroport, $date->toDateString());
        $departs = array_map(fn ($v) => FlightService::normaliser($v, 'depart'), $donnees['departures']);
        $arrivees = array_map(fn ($v) => FlightService::normaliser($v, 'arrivee'), $donnees['arrivals']);

        return view('site.vols', [
            'aeroport' => $aeroport,
            'aeroports' => self::AEROPORTS,
            'date' => $date->locale('fr'),
            'departs' => $departs,
            'arrivees' => $arrivees,
            'erreur' => $donnees['erreur'],
        ]);
    }
}
