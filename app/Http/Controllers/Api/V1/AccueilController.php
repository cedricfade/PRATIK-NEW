<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ApiResource;
use App\Http\Resources\Api\BienResource;
use App\Http\Resources\Api\EntrepriseResource;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\PubApp;
use App\Support\Hero;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/v1/accueil — tout ce qu'il faut pour l'écran d'accueil de l'application :
 * en-têtes (image + textes réglés dans Admin › Apparence), chiffres clés,
 * pros VIP (TOP 12) avec logo et dernières annonces immobilières.
 */
class AccueilController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $chiffres = Cache::remember('api:accueil:chiffres', 21600, fn () => [
            'entreprises' => Entreprise::fichesAnnuaire()->count(),
            'biens' => Immobilier::publie()->count(),
            'ventes' => Immobilier::publie()->vente()->count(),
            'locations' => Immobilier::publie()->location()->count(),
            'pharmacies' => DB::table('thematique_annuaire')->where('SOUS_CATEGORIE_ID', config('pratik.pharmacie_sous_categorie'))->distinct()->count('ENTREPRISE_ID'),
        ]);

        $partenaires = Cache::remember('api:accueil:partenaires', 3600, fn () => Entreprise::fichesAnnuaire()
            ->whereHas('annuaire', fn ($q) => $q->where('VIP', 1)->where('LOGO', '!=', '')
                ->whereRaw('LOGO NOT REGEXP ?', ['^(logo_entrprise_default|default-logo-flou[0-9]*|default)\\.']))
            ->with(['annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1,CLIENT,VIP,TEL_1,TEL_2,CEL_1,CEL_2', 'commune:ID,DESIGNATION,LIEN'])
            ->inRandomOrder()
            ->limit(12)
            ->get(['ID', 'TITRE', 'SIGLE', 'LIEN', 'ACTIVITE', 'COMMUNE_ID']));

        $biens = Cache::remember('api:accueil:biens', 900, fn () => Immobilier::publie()
            ->with(['entreprise:ID,TITRE,LIEN,COMMUNE_ID', 'entreprise.commune:ID,DESIGNATION,LIEN', 'categorie:ID,DESIGNATION', 'type:ID,DESIGNATION'])
            ->orderByDesc('ID')
            ->limit(6)
            ->get());

        $hero = fn (string $page) => collect(Hero::pour($page, $chiffres))
            ->except('voile')
            ->merge([
                'voile' => Hero::pour($page, $chiffres)['voile'],
                'image_url' => ApiResource::urlAbsolue(Hero::pour($page, $chiffres)['image_url']),
                'image_mobile_url' => ApiResource::urlAbsolue(Hero::pour($page, $chiffres)['image_mobile_url']),
            ]);

        $chiffresHero = Hero::chiffres();

        // Carrousel publicitaire de l'accueil de l'app (Admin › Apparence › Application mobile).
        $pubs = PubApp::enLigne()->get()->map(fn (PubApp $p) => [
            'id' => $p->id,
            'titre' => $p->titre,
            'image_url' => ApiResource::urlAbsolue($p->image_url),
            'lien' => $p->lien,
        ])->values();

        return response()->json(['data' => [
            'pubs' => $pubs,
            'hero' => $hero('accueil'),
            'hero_immobilier' => $hero('immobilier'),
            'chiffres' => $chiffres + [
                'afficher' => $chiffresHero['afficher'],
                'visiteurs' => $chiffresHero['visiteurs'],
                'visiteurs_libelle' => $chiffresHero['visiteurs_libelle'],
            ],
            'partenaires' => EntrepriseResource::collection($partenaires),
            'derniers_biens' => BienResource::collection($biens),
        ]]);
    }
}
