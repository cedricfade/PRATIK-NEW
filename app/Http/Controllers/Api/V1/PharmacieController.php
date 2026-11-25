<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PeriodeGardeResource;
use App\Http\Resources\Api\PharmacieResource;
use App\Models\Entreprise;
use App\Models\PeriodeGarde;
use App\Models\PharmacieGarde;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PharmacieController extends Controller
{
    use Concerns;

    private const RAYON_TERRE_KM = 6371;

    /** Sans ?commune : période + communes ayant des gardes. Avec ?commune : la liste des pharmacies de garde. */
    public function garde(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $aujourdhui = now()->toDateString();
        $periode = Cache::remember('api:periode-garde:'.$aujourdhui, 600, fn () => PeriodeGarde::courante());
        $commune = $this->commune($request);

        if (! $commune) {
            $communes = Cache::remember('api:gardes-communes:'.$aujourdhui, 600, fn () => PharmacieGarde::enCours($aujourdhui)
                ->join('entreprises', 'entreprises.ID', '=', 'pharmacie_garde.ENTREPRISE_ID')
                ->join('communes', 'communes.ID', '=', 'entreprises.COMMUNE_ID')
                ->groupBy('communes.ID', 'communes.DESIGNATION', 'communes.LIEN')
                ->orderBy('communes.DESIGNATION')
                ->get(['communes.ID', 'communes.DESIGNATION', 'communes.LIEN', DB::raw('COUNT(DISTINCT pharmacie_garde.ENTREPRISE_ID) as total')]));

            return response()->json(['data' => [
                'periode' => $periode ? new PeriodeGardeResource($periode) : null,
                'communes' => $communes->map(fn ($c) => [
                    'id' => $c->ID,
                    'designation' => $c->DESIGNATION,
                    'lien' => $c->LIEN,
                    'abidjan' => in_array((int) $c->ID, config('pratik.communes_abidjan'), true),
                    'nombre_pharmacies' => (int) $c->total,
                ])->values(),
            ]]);
        }

        $pharmacies = Entreprise::query()
            ->whereIn('ID', PharmacieGarde::enCours($aujourdhui)->select('ENTREPRISE_ID'))
            ->where('COMMUNE_ID', $commune->ID)
            ->with(['annuaire:ID,ENTREPRISE_ID,ADRESSE,LOCALISATION,TEL_1,TEL_2,CEL_1,CEL_2,EMAIL_1,EMAIL_2,LATITUDE,LONGITUDE', 'commune:ID,DESIGNATION,LIEN'])
            ->orderBy('TITRE')
            ->get(['ID', 'TITRE', 'LIEN', 'COMMUNE_ID']);

        return PharmacieResource::collection($pharmacies)->additional(['meta' => [
            'periode' => $periode ? new PeriodeGardeResource($periode) : null,
            'commune' => ['id' => $commune->ID, 'designation' => $commune->DESIGNATION, 'lien' => $commune->LIEN],
            'total' => $pharmacies->count(),
        ]]);
    }

    /** Pharmacies de garde autour d'un point, triées par distance (km). */
    public function proximite(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'rayon' => ['nullable', 'numeric', 'min:0.5', 'max:50'],
        ], [], ['lat' => 'latitude', 'lng' => 'longitude']);

        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];
        $rayon = (float) ($data['rayon'] ?? 5);

        // Boîte englobante : élimine la plupart des lignes avant le calcul trigonométrique.
        $dLat = $rayon / 111.045;
        $dLng = $rayon / (111.045 * max(cos(deg2rad($lat)), 0.01));

        $distance = '('.self::RAYON_TERRE_KM.' * ACOS(LEAST(1, GREATEST(-1,
            COS(RADIANS(?)) * COS(RADIANS(an.LATITUDE)) * COS(RADIANS(an.LONGITUDE) - RADIANS(?))
            + SIN(RADIANS(?)) * SIN(RADIANS(an.LATITUDE))
        ))))';

        $lignes = DB::table('annuaires as an')
            ->join('entreprises as en', 'en.ID', '=', 'an.ENTREPRISE_ID')
            ->whereIn('an.ENTREPRISE_ID', PharmacieGarde::enCours()->select('ENTREPRISE_ID'))
            ->whereBetween('an.LATITUDE', [$lat - $dLat, $lat + $dLat])
            ->whereBetween('an.LONGITUDE', [$lng - $dLng, $lng + $dLng])
            ->select('en.ID')
            ->selectRaw($distance.' AS distance_km', [$lat, $lng, $lat])
            ->having('distance_km', '<=', $rayon)
            ->orderBy('distance_km')
            ->limit(50)
            ->get();

        $distances = $lignes->pluck('distance_km', 'ID');

        $pharmacies = Entreprise::whereIn('ID', $distances->keys())
            ->with(['annuaire:ID,ENTREPRISE_ID,ADRESSE,LOCALISATION,TEL_1,TEL_2,CEL_1,CEL_2,EMAIL_1,EMAIL_2,LATITUDE,LONGITUDE', 'commune:ID,DESIGNATION,LIEN'])
            ->get(['ID', 'TITRE', 'LIEN', 'COMMUNE_ID'])
            ->each(fn ($p) => $p->distance_km = (float) $distances[$p->ID])
            ->sortBy('distance_km')
            ->values();

        return PharmacieResource::collection($pharmacies)->additional(['meta' => [
            'position' => ['lat' => $lat, 'lng' => $lng],
            'rayon_km' => $rayon,
            'total' => $pharmacies->count(),
        ]]);
    }
}
