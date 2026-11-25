<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Entreprise;
use App\Models\PharmacieGarde;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PharmacieController extends Controller
{
    public function index(): View
    {
        $aujourdhui = now()->toDateString();

        $communes = Cache::remember('pharmacies:communes:'.$aujourdhui, 600, fn () => PharmacieGarde::enCours($aujourdhui)
            ->join('entreprises', 'entreprises.ID', '=', 'pharmacie_garde.ENTREPRISE_ID')
            ->join('communes', 'communes.ID', '=', 'entreprises.COMMUNE_ID')
            ->groupBy('communes.ID', 'communes.DESIGNATION', 'communes.LIEN')
            ->orderBy('communes.DESIGNATION')
            ->get([
                'communes.ID',
                'communes.DESIGNATION',
                'communes.LIEN',
                DB::raw('COUNT(DISTINCT pharmacie_garde.ENTREPRISE_ID) as total'),
            ]));

        $abidjanIds = config('pratik.communes_abidjan');
        $abidjan = $communes->filter(fn ($c) => in_array((int) $c->ID, $abidjanIds, true))->values();
        $interieur = $communes->reject(fn ($c) => in_array((int) $c->ID, $abidjanIds, true))->values();
        $total = $communes->sum('total');

        return view('site.pharmacies.index', compact('abidjan', 'interieur', 'total'));
    }

    public function commune(string $lien): View
    {
        $commune = Commune::where('LIEN', $lien)->firstOrFail();

        $pharmacies = $this->pharmaciesDeGarde()
            ->where('entreprises.COMMUNE_ID', $commune->ID)
            ->orderBy('entreprises.TITRE')
            ->paginate(25);

        $marqueurs = $this->sansPointsAberrants($pharmacies->getCollection()
            ->filter(fn ($p) => $p->annuaire?->a_geoloc)
            ->map(fn ($p) => [
                'lat' => (float) $p->annuaire->LATITUDE,
                'lng' => (float) $p->annuaire->LONGITUDE,
                'title' => $p->TITRE,
                'subtitle' => $p->annuaire->telephones[0] ?? null,
            ])->values());

        $autres = Cache::remember('pharmacies:communes-liens:'.now()->toDateString(), 600, fn () => PharmacieGarde::enCours()
            ->join('entreprises', 'entreprises.ID', '=', 'pharmacie_garde.ENTREPRISE_ID')
            ->join('communes', 'communes.ID', '=', 'entreprises.COMMUNE_ID')
            ->distinct()
            ->orderBy('communes.DESIGNATION')
            ->get(['communes.DESIGNATION', 'communes.LIEN']));

        return view('site.pharmacies.commune', compact('commune', 'pharmacies', 'marqueurs', 'autres'));
    }

    /**
     * Pharmacies de garde proches d'une position (GPS du téléphone).
     * Préfiltre par boîte englobante (rapide), puis calcul de la distance réelle (haversine).
     */
    public function autourDeMoi(Request $request): View
    {
        $request->merge([
            'latitude' => $request->input('latitude', $request->input('lat')),
            'longitude' => $request->input('longitude', $request->input('lng')),
        ]);

        $position = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'latitude.required' => 'Position introuvable : autorisez la localisation puis réessayez.',
            'longitude.required' => 'Position introuvable : autorisez la localisation puis réessayez.',
        ]);

        $lat = (float) $position['latitude'];
        $lng = (float) $position['longitude'];

        $rayon = 3;
        $pharmacies = $this->autour($lat, $lng, $rayon);

        if ($pharmacies->isEmpty()) {
            $rayon = 10;
            $pharmacies = $this->autour($lat, $lng, $rayon);
        }

        $marqueurs = $pharmacies->map(fn ($p) => [
            'lat' => (float) $p->annuaire->LATITUDE,
            'lng' => (float) $p->annuaire->LONGITUDE,
            'title' => $p->TITRE,
            'subtitle' => number_format($p->distance, 1, ',', ' ').' km',
        ])->values();

        return view('site.pharmacies.autour', compact('pharmacies', 'marqueurs', 'lat', 'lng', 'rayon'));
    }

    private function autour(float $lat, float $lng, int $rayonKm)
    {
        $dLat = $rayonKm / 111.045;
        $dLng = $rayonKm / (111.045 * max(cos(deg2rad($lat)), 0.01));

        return $this->pharmaciesDeGarde()
            ->join('annuaires as geo', 'geo.ENTREPRISE_ID', '=', 'entreprises.ID')
            ->whereBetween('geo.LATITUDE', [$lat - $dLat, $lat + $dLat])
            ->whereBetween('geo.LONGITUDE', [$lng - $dLng, $lng + $dLng])
            ->selectRaw(
                '(6371 * ACOS(LEAST(1, COS(RADIANS(?)) * COS(RADIANS(geo.LATITUDE)) * COS(RADIANS(geo.LONGITUDE) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(geo.LATITUDE))))) AS distance',
                [$lat, $lng, $lat],
            )
            ->groupBy('entreprises.ID', 'entreprises.TITRE', 'entreprises.LIEN', 'entreprises.COMMUNE_ID', 'geo.LATITUDE', 'geo.LONGITUDE')
            ->having('distance', '<=', $rayonKm)
            ->orderBy('distance')
            ->limit(30)
            ->get();
    }

    /**
     * Certaines fiches ont des coordonnées GPS erronées (pharmacie de Cocody placée à Bouaké…) :
     * on écarte les points à plus de 25 km de la médiane pour garder une carte lisible.
     */
    private function sansPointsAberrants($marqueurs)
    {
        if ($marqueurs->count() < 3) {
            return $marqueurs;
        }

        $mLat = $marqueurs->pluck('lat')->median();
        $mLng = $marqueurs->pluck('lng')->median();

        return $marqueurs->filter(function ($m) use ($mLat, $mLng) {
            $dLat = ($m['lat'] - $mLat) * 111;
            $dLng = ($m['lng'] - $mLng) * 111 * cos(deg2rad($mLat));

            return sqrt($dLat ** 2 + $dLng ** 2) <= 25;
        })->values();
    }

    /** Entreprises actuellement de garde (sans doublon), avec fiche annuaire et commune. */
    private function pharmaciesDeGarde()
    {
        return Entreprise::query()
            ->select(['entreprises.ID', 'entreprises.TITRE', 'entreprises.LIEN', 'entreprises.COMMUNE_ID'])
            ->whereIn('entreprises.ID', PharmacieGarde::enCours()->select('ENTREPRISE_ID'))
            ->with([
                'annuaire:ID,ENTREPRISE_ID,ADRESSE,LOCALISATION,GEOLOCALISATION,LATITUDE,LONGITUDE,TEL_1,TEL_2,CEL_1,CEL_2,EMAIL_1,EMAIL_2,LOGO,LOGO1',
                'commune:ID,DESIGNATION,LIEN',
            ]);
    }
}
