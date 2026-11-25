<?php

namespace App\Support;

use App\Models\Entreprise;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Ajout de vues depuis le backoffice (Admin › Vues).
 *
 * Les vues ajoutées vont dans entreprises.VUES_BONUS ; le compteur réel COMPTEUR n'est
 * jamais modifié. Le site affiche la somme des deux. Chaque opération est une seule
 * requête UPDATE (rapide même sur les ~50 000 fiches de l'annuaire) et est historisée.
 */
class AugmentationVues
{
    /** Périmètres proposés dans le backoffice. */
    public const PERIMETRES = [
        'annuaire_tous' => ['Annuaire', 'Toutes les entreprises de l\'annuaire'],
        'annuaire_vip' => ['Annuaire', 'Entreprises VIP (TOP 12)'],
        'annuaire_categorie' => ['Annuaire', 'Les entreprises d\'une catégorie'],
        'immo_biens' => ['Immobilier', 'Toutes les annonces en ligne'],
        'immo_vedette' => ['Immobilier', 'Annonces à la une et de prestige'],
        'immo_annonceur' => ['Immobilier', 'Les annonces d\'un annonceur'],
        'immo_agences' => ['Immobilier', 'Pages des agences et promoteurs'],
        'fiche' => ['Au cas par cas', 'Une entreprise ou une annonce précise'],
    ];

    /** @param array{categorie?: int|null, annonceur?: int|null, fiche?: int|null} $cible */
    public static function requete(string $perimetre, array $cible = []): Builder
    {
        $q = Entreprise::query();

        return match ($perimetre) {
            'annuaire_tous' => $q->where('TYPE_ID', config('pratik.types.annuaire')),
            'annuaire_vip' => $q->where('TYPE_ID', config('pratik.types.annuaire'))
                ->whereIn('ID', DB::table('annuaires')->select('ENTREPRISE_ID')->where('VIP', 1)),
            'annuaire_categorie' => $q->where('TYPE_ID', config('pratik.types.annuaire'))
                ->whereIn('ID', DB::table('thematique_annuaire')->select('ENTREPRISE_ID')->where('CATEGORIE_ID', (int) ($cible['categorie'] ?? 0))),
            'immo_biens' => $q->whereIn('ID', DB::table('immobilier')->select('ENTREPRISE_ID')->where('VALIDATION', 'OUI')),
            'immo_vedette' => $q->whereIn('ID', DB::table('immobilier')->select('ENTREPRISE_ID')->where('VALIDATION', 'OUI')
                ->where(fn ($w) => $w->where('FLASH', 'OUI')->orWhere('LUXE', 'OUI'))),
            'immo_annonceur' => $q->whereIn('ID', DB::table('immobilier')->select('ENTREPRISE_ID')->where('UTILISATEUR', (int) ($cible['annonceur'] ?? 0))),
            'immo_agences' => $q->whereIn('ID', DB::table('users')->select('entreprise_id')->where('role_id', config('pratik.roles.immobilier'))->whereNotNull('entreprise_id')),
            'fiche' => $q->whereKey((int) ($cible['fiche'] ?? 0)),
            default => throw new InvalidArgumentException("Périmètre inconnu : {$perimetre}"),
        };
    }

    /** Ajoute entre $min et $max vues (tirées au hasard pour chaque fiche) ; renvoie le nombre de fiches touchées. */
    public static function ajouter(string $perimetre, int $min, int $max, array $cible = []): int
    {
        $min = max(0, $min);
        $max = max($min, $max);
        $ecart = $max - $min + 1;

        // Valeurs entières validées : pas d'injection possible dans l'expression SQL.
        $expression = $min === $max
            ? "VUES_BONUS + {$min}"
            : "VUES_BONUS + {$min} + FLOOR(RAND() * {$ecart})";

        return self::requete($perimetre, $cible)->update(['VUES_BONUS' => DB::raw($expression)]);
    }

    /** Remet à zéro les vues ajoutées (les vues réelles ne bougent pas). */
    public static function remettreAZero(string $perimetre, array $cible = []): int
    {
        return self::requete($perimetre, $cible)->where('VUES_BONUS', '>', 0)->update(['VUES_BONUS' => 0]);
    }

    /** @return array{fiches: int, reelles: int, ajoutees: int} */
    public static function resume(string $perimetre, array $cible = []): array
    {
        $r = self::requete($perimetre, $cible)
            ->selectRaw('COUNT(*) as fiches, COALESCE(SUM(COMPTEUR), 0) as reelles, COALESCE(SUM(VUES_BONUS), 0) as ajoutees')
            ->toBase()
            ->first();

        return ['fiches' => (int) $r->fiches, 'reelles' => (int) $r->reelles, 'ajoutees' => (int) $r->ajoutees];
    }
}
