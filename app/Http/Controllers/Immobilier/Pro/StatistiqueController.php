<?php

namespace App\Http\Controllers\Immobilier\Pro;

use App\Http\Controllers\Controller;
use App\Models\Immobilier;
use App\Models\Statistique;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Statistiques par période, comme sur l'ancien site : on choisit une année
 * et, si on veut, un mois. Compte la société de l'annonceur et chacun de ses biens.
 */
class StatistiqueController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $suivies = $user->entreprisesSuivies();

        $annees = Statistique::whereIn('ENTREPRISE_ID', $suivies)
            ->distinct()
            ->pluck('ANNEE')
            ->map(fn ($a) => (int) $a)
            ->push(now()->year)
            ->filter(fn ($a) => $a >= 2020 && $a <= now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        $annee = (int) $request->query('annee', now()->year);
        $annee = $annees->contains($annee) ? $annee : now()->year;
        $moisChoisi = (int) $request->query('mois', 0);
        $moisChoisi = $moisChoisi >= 1 && $moisChoisi <= 12 ? $moisChoisi : 0;

        $lignes = Statistique::whereIn('ENTREPRISE_ID', $suivies)
            ->where('ANNEE', (string) $annee)
            ->whereIn('TYPE_STAT', [Statistique::VUE, Statistique::APPEL, Statistique::EMAIL])
            ->get(['TYPE_STAT', 'MOIS', 'STATS']);

        $somme = fn ($lignes, int $type) => (int) $lignes->where('TYPE_STAT', $type)->sum('STATS');

        $mois = [];
        $dernierMois = $annee === now()->year ? now()->month : 12;
        for ($m = 1; $m <= $dernierMois; $m++) {
            $duMois = $lignes->filter(fn ($l) => (int) $l->MOIS === $m);
            $date = Carbon::create($annee, $m, 1)->locale('fr');
            $mois[] = [
                'numero' => $m,
                'libelle' => $date->isoFormat('MMM'),
                'complet' => $date->isoFormat('MMMM YYYY'),
                'vues' => $somme($duMois, Statistique::VUE),
                'appels' => $somme($duMois, Statistique::APPEL),
                'emails' => $somme($duMois, Statistique::EMAIL),
            ];
        }

        $periode = $moisChoisi ? $lignes->filter(fn ($l) => (int) $l->MOIS === $moisChoisi) : $lignes;

        // Vues de chaque annonce depuis sa publication (compteur de la fiche du bien).
        $parBien = Immobilier::where('immobilier.UTILISATEUR', $user->id)
            ->join('entreprises', 'entreprises.ID', '=', 'immobilier.ENTREPRISE_ID')
            ->orderByRaw('entreprises.COMPTEUR + entreprises.VUES_BONUS desc')
            ->limit(50)
            ->get(['immobilier.ID', 'immobilier.VALIDATION', 'immobilier.DATE_LOG', 'entreprises.TITRE', 'entreprises.LIEN', 'entreprises.COMPTEUR', 'entreprises.VUES_BONUS']);

        return view('immobilier.pro.statistiques', [
            'annees' => $annees,
            'annee' => $annee,
            'moisChoisi' => $moisChoisi,
            'libellePeriode' => $moisChoisi
                ? Carbon::create($annee, $moisChoisi, 1)->locale('fr')->isoFormat('MMMM YYYY')
                : 'année '.$annee,
            'totaux' => [
                'vues' => $somme($periode, Statistique::VUE),
                'appels' => $somme($periode, Statistique::APPEL),
                'emails' => $somme($periode, Statistique::EMAIL),
            ],
            'mois' => $mois,
            'max' => max(1, collect($mois)->max(fn ($m) => max($m['vues'], $m['appels'], $m['emails']))),
            'parBien' => $parBien,
        ]);
    }
}
