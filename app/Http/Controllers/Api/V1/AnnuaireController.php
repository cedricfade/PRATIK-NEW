<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategorieAnnuaireResource;
use App\Http\Resources\Api\EntrepriseDetailResource;
use App\Http\Resources\Api\EntrepriseResource;
use App\Http\Resources\Api\SousCategorieResource;
use App\Models\Annuaire;
use App\Models\CategorieAnnuaire;
use App\Models\Entreprise;
use App\Models\SousCategorie;
use App\Models\ThematiqueAnnuaire;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnnuaireController extends Controller
{
    use Concerns;

    private const COLONNES_ANNUAIRE = 'annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1,CLIENT,VIP,TEL_1,TEL_2,CEL_1,CEL_2,ADRESSE,LATITUDE,LONGITUDE';

    public function categories(): AnonymousResourceCollection
    {
        $categories = Cache::remember('api:annuaire:categories', 21600, function () {
            $totaux = DB::table('thematique_annuaire')
                ->groupBy('CATEGORIE_ID')
                ->pluck(DB::raw('COUNT(DISTINCT ENTREPRISE_ID)'), 'CATEGORIE_ID');

            return CategorieAnnuaire::orderBy('DESIGNATION')
                ->get(['ID', 'DESIGNATION', 'DESCRIPTION', 'LIEN'])
                ->each(fn ($c) => $c->nombre_entreprises = (int) ($totaux[$c->ID] ?? 0));
        });

        return CategorieAnnuaireResource::collection($categories);
    }

    public function sousCategories(string $lien): AnonymousResourceCollection
    {
        $categorie = CategorieAnnuaire::where('LIEN', $lien)->firstOrFail(['ID', 'DESIGNATION', 'LIEN']);

        $sous = Cache::remember('api:annuaire:sous-categories:'.$categorie->ID, 21600, function () use ($categorie) {
            $totaux = DB::table('thematique_annuaire')
                ->where('CATEGORIE_ID', $categorie->ID)
                ->groupBy('SOUS_CATEGORIE_ID')
                ->pluck(DB::raw('COUNT(DISTINCT ENTREPRISE_ID)'), 'SOUS_CATEGORIE_ID');

            return SousCategorie::where('CATEGORIE_ID', $categorie->ID)
                ->where('ACTIVATION', 1)
                ->orderBy('DESIGNATION')
                ->get(['ID', 'CATEGORIE_ID', 'DESIGNATION', 'LIEN', 'DESCRIPTIF', 'PUBS'])
                ->each(fn ($s) => $s->nombre_entreprises = (int) ($totaux[$s->ID] ?? 0));
        });

        return SousCategorieResource::collection($sous)->additional([
            'categorie' => new CategorieAnnuaireResource($categorie),
        ]);
    }

    public function entreprisesParSousCategorie(Request $request, int $id): AnonymousResourceCollection
    {
        $sous = SousCategorie::findOrFail($id, ['ID', 'CATEGORIE_ID', 'DESIGNATION', 'LIEN', 'DESCRIPTIF', 'PUBS']);
        $commune = $this->commune($request);

        $entreprises = Entreprise::query()
            ->whereIn('entreprises.ID', ThematiqueAnnuaire::select('ENTREPRISE_ID')->where('SOUS_CATEGORIE_ID', $sous->ID))
            ->when($commune, fn ($q) => $q->where('entreprises.COMMUNE_ID', $commune->ID))
            ->tap(fn ($q) => $this->clientsDabord($q))
            ->with([self::COLONNES_ANNUAIRE, 'commune:ID,DESIGNATION,LIEN'])
            ->select(['entreprises.ID', 'entreprises.TITRE', 'entreprises.SIGLE', 'entreprises.LIEN', 'entreprises.ACTIVITE', 'entreprises.COMMUNE_ID'])
            ->paginate($this->parPage($request))
            ->withQueryString();

        return EntrepriseResource::collection($entreprises)->additional([
            'sous_categorie' => new SousCategorieResource($sous),
        ]);
    }

    public function recherche(Request $request): AnonymousResourceCollection
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']], [], ['q' => 'recherche']);
        $commune = $this->commune($request);

        $entreprises = Entreprise::query()
            ->whereIn('entreprises.TYPE_ID', [config('pratik.types.annuaire'), config('pratik.types.pharmacie')])
            ->recherche($request->string('q')->toString())
            ->when($commune, fn ($q) => $q->where('entreprises.COMMUNE_ID', $commune->ID))
            ->tap(fn ($q) => $this->clientsDabord($q))
            ->with([self::COLONNES_ANNUAIRE, 'commune:ID,DESIGNATION,LIEN'])
            ->select(['entreprises.ID', 'entreprises.TITRE', 'entreprises.SIGLE', 'entreprises.LIEN', 'entreprises.ACTIVITE', 'entreprises.COMMUNE_ID'])
            ->paginate($this->parPage($request))
            ->withQueryString();

        return EntrepriseResource::collection($entreprises);
    }

    public function show(string $lien): EntrepriseDetailResource
    {
        $entreprise = Entreprise::where('LIEN', $lien)
            ->whereIn('TYPE_ID', [config('pratik.types.annuaire'), config('pratik.types.pharmacie')])
            ->with([
                'annuaire.photos:ID,ANNUAIRE_ID,IMAGE',
                'annuaire.departements',
                'commune:ID,DESIGNATION,LIEN',
                'sousCategories:sous_categorie.ID,sous_categorie.CATEGORIE_ID,sous_categorie.DESIGNATION,sous_categorie.LIEN',
                'sousCategories.categorie:ID,DESIGNATION,LIEN',
            ])
            ->firstOrFail();

        $entreprise->incrementerVues();

        return new EntrepriseDetailResource($entreprise);
    }

    /** Les pros (fiches VIP, badge TOP 12) remontent en tête, puis la priorité, puis l'ordre alphabétique. */
    private function clientsDabord(Builder $query): void
    {
        $query->orderByDesc(
            Annuaire::selectRaw('MAX(VIP = 1)')->whereColumn('annuaires.ENTREPRISE_ID', 'entreprises.ID')
        )
            ->orderByDesc('entreprises.PRIORITE')
            ->orderBy('entreprises.TITRE');
    }
}
