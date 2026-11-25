<?php

namespace App\Http\Controllers\Immobilier\Support;

use App\Models\Immobilier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Construit la requête des biens publiés à partir des filtres de l'URL :
 * categorie, commune, prix_min, prix_max, chambres, luxe, type, tri.
 */
class RechercheBiens
{
    /** Expression SQL du prix numérique (PRIX est stocké en texte). */
    public const PRIX_SQL = "CAST(REPLACE(REPLACE(REPLACE(immobilier.PRIX, ' ', ''), '.', ''), ',', '') AS UNSIGNED)";

    public const TRIS = [
        'recent' => 'Plus récents',
        'prix_asc' => 'Prix croissant',
        'prix_desc' => 'Prix décroissant',
    ];

    /** @return array<string, mixed> */
    public static function filtres(Request $request): array
    {
        $entier = fn (string $cle) => ($v = preg_replace('/\D+/', '', (string) $request->query($cle))) !== '' ? (int) $v : null;

        return array_filter([
            'categorie' => $entier('categorie'),
            'commune' => $entier('commune'),
            'prix_min' => $entier('prix_min'),
            'prix_max' => $entier('prix_max'),
            'chambres' => $entier('chambres'),
            'type' => in_array($entier('type'), [1, 2, 3, 4], true) ? $entier('type') : null,
            'luxe' => $request->boolean('luxe') ? 1 : null,
            'tri' => array_key_exists((string) $request->query('tri'), self::TRIS) ? $request->query('tri') : null,
        ], fn ($v) => $v !== null && $v !== 0 && $v !== '');
    }

    /**
     * @param  'vente'|'location'|null  $transaction
     */
    public static function requete(array $filtres, ?string $transaction = null): Builder
    {
        $query = Immobilier::query()
            ->publie()
            ->select('immobilier.*')
            ->join('entreprises', 'entreprises.ID', '=', 'immobilier.ENTREPRISE_ID')
            ->with([
                'entreprise:ID,TITRE,LIEN,COMMUNE_ID',
                'entreprise.commune:ID,DESIGNATION,LIEN',
                'categorie:ID,DESIGNATION',
            ]);

        match ($transaction) {
            'vente' => $query->vente(),
            'location' => $query->location(),
            default => null,
        };

        $query
            ->when($filtres['categorie'] ?? null, fn ($q, $v) => $q->where('immobilier.CATEGORIE_IMMOBILIER_ID', $v))
            ->when($filtres['commune'] ?? null, fn ($q, $v) => $q->where('entreprises.COMMUNE_ID', $v))
            ->when($filtres['type'] ?? null, fn ($q, $v) => $q->where('immobilier.TYPE_IMMOBILIER_ID', $v))
            ->when($filtres['chambres'] ?? null, fn ($q, $v) => $q->where('immobilier.CHAMBRE', '>=', $v))
            ->when($filtres['luxe'] ?? null, fn ($q) => $q->luxe())
            ->when($filtres['prix_min'] ?? null, fn ($q, $v) => $q->whereRaw(self::PRIX_SQL.' >= ?', [$v]))
            ->when($filtres['prix_max'] ?? null, fn ($q, $v) => $q->whereRaw(self::PRIX_SQL.' > 0 AND '.self::PRIX_SQL.' <= ?', [$v]));

        match ($filtres['tri'] ?? 'recent') {
            'prix_asc' => $query->orderByRaw(self::PRIX_SQL.' = 0')->orderByRaw(self::PRIX_SQL.' asc'),
            'prix_desc' => $query->orderByRaw(self::PRIX_SQL.' desc'),
            default => $query->orderByDesc('immobilier.ID'),
        };

        return $query;
    }

    /**
     * Marqueurs de carte pour une liste de biens (entreprise chargée).
     *
     * @return list<array<string, mixed>>
     */
    public static function marqueurs(iterable $biens): array
    {
        $marqueurs = [];

        foreach ($biens as $bien) {
            if (! $bien->a_geoloc || ! $bien->entreprise?->LIEN) {
                continue;
            }

            $marqueurs[] = [
                'lat' => (float) $bien->LATITUDE,
                'lng' => (float) $bien->LONGITUDE,
                'title' => $bien->entreprise->TITRE,
                'subtitle' => $bien->prix_affiche,
                'url' => route('immo.bien', $bien->entreprise->LIEN),
                'image' => $bien->image_url,
            ];
        }

        return $marqueurs;
    }
}
