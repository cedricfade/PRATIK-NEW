<?php

namespace App\Http\Controllers\Api\V1\Pro;

use App\Http\Controllers\Controller;
use App\Models\Immobilier;
use App\Models\Statistique;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Vues, appels et e-mails reçus par l'annonceur (mois en cours, année, détail mensuel). */
class StatistiqueController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $annee = (int) $request->integer('annee', now()->year);
        $types = [Statistique::VUE => 'vues', Statistique::APPEL => 'appels', Statistique::EMAIL => 'emails'];

        // Société de l'annonceur + fiche de chacun de ses biens (voir User::entreprisesSuivies()).
        $lignes = Statistique::whereIn('ENTREPRISE_ID', $user->entreprisesSuivies())
            ->where('ANNEE', (string) $annee)
            ->whereIn('TYPE_STAT', array_keys($types))
            ->get(['TYPE_STAT', 'MOIS', 'STATS']);

        $parMois = [];
        for ($m = 1; $m <= 12; $m++) {
            $parMois[] = ['mois' => $m] + collect($types)->mapWithKeys(fn ($cle, $type) => [
                $cle => (int) $lignes->where('TYPE_STAT', $type)->where('MOIS', (string) $m)->sum('STATS'),
            ])->all();
        }

        $moisCourant = $annee === now()->year ? $parMois[now()->month - 1] : null;

        return response()->json(['data' => [
            'annee' => $annee,
            'mois_en_cours' => $moisCourant ? collect($moisCourant)->except('mois')->all() : null,
            'total_annee' => collect($types)->mapWithKeys(fn ($cle) => [$cle => array_sum(array_column($parMois, $cle))])->all(),
            'par_mois' => $parMois,
            'biens' => [
                'publies' => Immobilier::where('UTILISATEUR', $user->id)->where('VALIDATION', 'OUI')->count(),
                'en_attente' => Immobilier::where('UTILISATEUR', $user->id)->where(fn ($q) => $q->where('VALIDATION', '!=', 'OUI')->orWhereNull('VALIDATION'))->count(),
            ],
        ]]);
    }
}
