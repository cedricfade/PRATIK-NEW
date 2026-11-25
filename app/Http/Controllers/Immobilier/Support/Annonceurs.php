<?php

namespace App\Http\Controllers\Immobilier\Support;

use App\Support\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Annonceurs immobiliers (agences, promoteurs, aménageurs fonciers),
 * calculés à partir des biens publiés — fini les listes d'ID codées en dur.
 *
 * Un annonceur = un compte users (immobilier.UTILISATEUR) rattaché à une
 * entreprise (users.entreprise_id) qui porte le nom, le lien et le logo.
 */
class Annonceurs
{
    /** Catégorie d'annonceur => TYPE_IMMOBILIER_ID concernés. */
    public const CATEGORIES = [
        'agences' => [1, 2],
        'promoteurs' => [3],
        'amenageurs' => [4],
    ];

    /** Logos "par défaut" enregistrés par l'ancien site : traités comme absents. */
    private const LOGOS_VIDES = ['logo_entrprise_default.png', 'default.png', 'default.jpg'];

    /**
     * @return list<array{user_id:int, entreprise_id:int, titre:string, lien:string, logo_url:?string, commune:?string, total:int}>
     */
    public static function liste(string $categorie, ?int $limite = null): array
    {
        $types = self::CATEGORIES[$categorie] ?? [1, 2, 3, 4];

        $liste = Cache::remember('immo:annonceurs:'.$categorie, 1800, function () use ($types) {
            return DB::table('immobilier')
                ->join('users', 'users.id', '=', 'immobilier.UTILISATEUR')
                ->join('entreprises', 'entreprises.ID', '=', 'users.entreprise_id')
                ->leftJoin('communes', 'communes.ID', '=', 'entreprises.COMMUNE_ID')
                ->where('immobilier.VALIDATION', 'OUI')
                ->whereIn('immobilier.TYPE_IMMOBILIER_ID', $types)
                ->whereNotNull('entreprises.LIEN')
                ->where('entreprises.LIEN', '!=', '')
                ->groupBy('users.id', 'users.entreprise_id', 'entreprises.TITRE', 'entreprises.LIEN', 'communes.DESIGNATION')
                ->orderByDesc('total')
                ->get([
                    'users.id as user_id',
                    'users.entreprise_id',
                    'entreprises.TITRE as titre',
                    'entreprises.LIEN as lien',
                    'communes.DESIGNATION as commune',
                    DB::raw('COUNT(DISTINCT immobilier.ID) as total'),
                    DB::raw('(SELECT a.LOGO FROM annuaires a WHERE a.ENTREPRISE_ID = users.entreprise_id ORDER BY a.ID LIMIT 1) as logo'),
                ])
                // Plusieurs comptes peuvent partager la même société : on regroupe par entreprise.
                ->groupBy('entreprise_id')
                ->map(function ($lignes) {
                    $premiere = $lignes->first();

                    return [
                        'user_id' => (int) $premiere->user_id,
                        'entreprise_id' => (int) $premiere->entreprise_id,
                        'titre' => (string) $premiere->titre,
                        'lien' => (string) $premiere->lien,
                        'commune' => $premiere->commune,
                        'logo' => $premiere->logo,
                        'total' => (int) $lignes->sum('total'),
                    ];
                })
                ->sortByDesc('total')
                ->values()
                ->all();
        });

        $liste = array_map(fn (array $a) => $a + ['logo_url' => self::logoUrl($a['logo'])], $liste);

        return $limite ? array_slice($liste, 0, $limite) : $liste;
    }

    public static function logoUrl(?string $logo): ?string
    {
        $logo = trim((string) $logo);

        if ($logo === '' || in_array(strtolower($logo), self::LOGOS_VIDES, true)) {
            return null;
        }

        return Media::url('logos', $logo);
    }

    public static function oublier(): void
    {
        foreach (array_keys(self::CATEGORIES) as $categorie) {
            Cache::forget('immo:annonceurs:'.$categorie);
        }
        Cache::forget('immo:annonceurs:tous');
    }
}
