<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AnnonceurResource;
use App\Http\Resources\Api\BienDetailResource;
use App\Http\Resources\Api\BienResource;
use App\Http\Resources\Api\CategorieImmobilierResource;
use App\Models\CategorieImmobilier;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\Statistique;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ImmobilierController extends Controller
{
    use Concerns;

    /** Relations nécessaires à BienResource (colonnes limitées). */
    public const RELATIONS_LISTE = [
        'entreprise:ID,TITRE,LIEN,COMMUNE_ID,COMPTEUR,VUES_BONUS',
        'entreprise.commune:ID,DESIGNATION,LIEN',
        'categorie:ID,DESIGNATION',
    ];

    public function categories(): AnonymousResourceCollection
    {
        $categories = Cache::remember('api:immo:categories', 3600, function () {
            $totaux = Immobilier::publie()->groupBy('CATEGORIE_IMMOBILIER_ID')->pluck(DB::raw('COUNT(*)'), 'CATEGORIE_IMMOBILIER_ID');

            return CategorieImmobilier::where(fn ($q) => $q->where('ACTIVATION', 1)->orWhereNull('ACTIVATION'))
                ->orderBy('DESIGNATION')
                ->get(['ID', 'DESIGNATION'])
                ->each(fn ($c) => $c->nombre_biens = (int) ($totaux[$c->ID] ?? 0));
        });

        return CategorieImmobilierResource::collection($categories);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $f = $request->validate([
            'transaction' => ['nullable', 'in:vente,location'],
            'categorie' => ['nullable', 'integer'],
            'prix_min' => ['nullable', 'integer', 'min:0'],
            'prix_max' => ['nullable', 'integer', 'min:0'],
            'chambres' => ['nullable', 'integer', 'min:0', 'max:20'],
            'luxe' => ['nullable', 'boolean'],
            'tri' => ['nullable', 'in:recent,prix_asc,prix_desc'],
        ]);
        $commune = $this->commune($request);
        $prix = 'CAST(immobilier.PRIX AS UNSIGNED)';

        $biens = Immobilier::publie()
            ->when(($f['transaction'] ?? null) === 'vente', fn ($q) => $q->vente())
            ->when(($f['transaction'] ?? null) === 'location', fn ($q) => $q->location())
            ->when($f['categorie'] ?? null, fn ($q, $c) => $q->where('immobilier.CATEGORIE_IMMOBILIER_ID', $c))
            ->when($commune, fn ($q) => $q->whereIn('immobilier.ENTREPRISE_ID', Entreprise::select('ID')->where('COMMUNE_ID', $commune->ID)))
            ->when(isset($f['prix_min']), fn ($q) => $q->whereRaw("$prix >= ?", [$f['prix_min']]))
            ->when(isset($f['prix_max']), fn ($q) => $q->whereRaw("$prix <= ?", [$f['prix_max']]))
            ->when($f['chambres'] ?? null, fn ($q, $n) => $q->where('immobilier.CHAMBRE', '>=', $n))
            ->when($request->boolean('luxe'), fn ($q) => $q->luxe())
            ->when(($f['tri'] ?? 'recent') === 'prix_asc', fn ($q) => $q->orderByRaw("$prix asc"))
            ->when(($f['tri'] ?? 'recent') === 'prix_desc', fn ($q) => $q->orderByRaw("$prix desc"))
            ->orderByDesc('immobilier.ID')
            ->with(self::RELATIONS_LISTE)
            ->paginate($this->parPage($request))
            ->withQueryString();

        return BienResource::collection($biens);
    }

    public function show(Request $request, string $lien): BienDetailResource
    {
        $bien = Immobilier::query()
            ->whereIn('ENTREPRISE_ID', Entreprise::select('ID')->where('LIEN', $lien)->where('TYPE_ID', config('pratik.types.immobilier')))
            ->with([
                ...self::RELATIONS_LISTE,
                'photos:ID,IMMOBILIER_ID,IMAGE',
                'auteur:id,name,email,entreprise_id,agent_tel,agent_cel',
                'auteur.entreprise:ID,TITRE,LIEN',
                'auteur.entreprise.annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1,TEL_1,TEL_2,CEL_1,CEL_2,EMAIL_1,EMAIL_2,WHATSAPP',
            ])
            ->firstOrFail();

        // Un bien non validé n'est visible que par son auteur ou un administrateur.
        $user = $request->user('api');
        $estProprietaire = $user && ($user->estAdmin() || (int) $bien->UTILISATEUR === (int) $user->id);
        abort_if($bien->VALIDATION !== 'OUI' && ! $estProprietaire, 404, 'Annonce introuvable.');

        if (! $estProprietaire) {
            $bien->entreprise?->incrementerVues();
            if ($bien->auteur?->entreprise_id) {
                Statistique::enregistrer((int) $bien->auteur->entreprise_id, Statistique::VUE);
            }
        }

        return new BienDetailResource($bien);
    }

    /** Agences (types 1-2), promoteurs (3) ou aménageurs fonciers (4) ayant au moins un bien publié. */
    public function annonceurs(Request $request): AnonymousResourceCollection
    {
        $request->validate(['type' => ['nullable', 'in:agence,promoteur,amenageur']]);
        $type = $request->query('type');
        $types = match ($type) {
            'agence' => [1, 2],
            'promoteur' => [3],
            'amenageur' => [4],
            default => [1, 2, 3, 4],
        };

        $annonceurs = Cache::remember('api:immo:annonceurs:'.($type ?: 'tous'), 1800, function () use ($types) {
            $totaux = Immobilier::publie()
                ->whereIn('TYPE_IMMOBILIER_ID', $types)
                ->whereNotNull('UTILISATEUR')
                ->groupBy('UTILISATEUR')
                ->pluck(DB::raw('COUNT(*)'), 'UTILISATEUR');

            return User::whereIn('id', $totaux->keys())
                ->whereNotNull('entreprise_id')
                ->with([
                    'entreprise:ID,TITRE,LIEN,COMMUNE_ID',
                    'entreprise.commune:ID,DESIGNATION,LIEN',
                    'entreprise.annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1,TEL_1,TEL_2,CEL_1,CEL_2,EMAIL_1,EMAIL_2',
                ])
                ->get(['id', 'name', 'entreprise_id', 'agent_tel', 'agent_cel'])
                ->each(fn ($u) => $u->nombre_biens = (int) $totaux[$u->id])
                ->sortByDesc('nombre_biens')
                ->values();
        });

        return AnnonceurResource::collection($annonceurs);
    }
}
