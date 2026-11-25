<?php

namespace App\Http\Controllers\Immobilier\Pro;

use App\Http\Controllers\Controller;
use App\Models\ContactEntreprise;
use App\Models\Immobilier;
use App\Models\Statistique;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $suivies = $user->entreprisesSuivies();

        $biens = Immobilier::where('UTILISATEUR', $user->id)
            ->selectRaw("SUM(VALIDATION = 'OUI') as publies, SUM(VALIDATION IS NULL OR VALIDATION <> 'OUI') as attente, COUNT(*) as total")
            ->first();

        // Statistiques de la société + de chaque bien (voir User::entreprisesSuivies()).
        $stats = ['mois' => [], 'annee' => []];
        $lignes = Statistique::whereIn('ENTREPRISE_ID', $suivies)
            ->where('ANNEE', (string) now()->year)
            ->get(['TYPE_STAT', 'MOIS', 'STATS']);

        foreach ([Statistique::VUE => 'vues', Statistique::APPEL => 'appels', Statistique::EMAIL => 'emails'] as $type => $cle) {
            $duType = $lignes->where('TYPE_STAT', $type);
            $stats['annee'][$cle] = (int) $duType->sum('STATS');
            $stats['mois'][$cle] = (int) $duType->where('MOIS', (string) now()->month)->sum('STATS');
        }

        // Total des consultations de tous les biens depuis leur publication (compteur de chaque fiche).
        $stats['vues_biens'] = (int) Immobilier::where('immobilier.UTILISATEUR', $user->id)
            ->join('entreprises', 'entreprises.ID', '=', 'immobilier.ENTREPRISE_ID')
            ->sum(\Illuminate\Support\Facades\DB::raw('entreprises.COMPTEUR + entreprises.VUES_BONUS'));

        $messages = ContactEntreprise::whereIn('entreprise_id', $suivies)->latest()->limit(5)->get();
        $nonLus = ContactEntreprise::whereIn('entreprise_id', $suivies)->where('status', 0)->count();

        $derniers = Immobilier::where('UTILISATEUR', $user->id)
            ->with(['entreprise:ID,TITRE,LIEN,COMPTEUR,VUES_BONUS', 'categorie:ID,DESIGNATION'])
            ->orderByDesc('ID')
            ->limit(5)
            ->get(['ID', 'ENTREPRISE_ID', 'CATEGORIE_IMMOBILIER_ID', 'TYPE_IMMOBILIER_ID', 'PRIX', 'PERIODE', 'VALIDATION', 'ILLUSTATION', 'DATE_LOG']);

        return view('immobilier.pro.dashboard', [
            'biens' => ['publies' => (int) $biens?->publies, 'attente' => (int) $biens?->attente, 'total' => (int) $biens?->total],
            'stats' => $stats,
            'messages' => $messages,
            'nonLus' => $nonLus,
            'derniers' => $derniers,
        ]);
    }
}
