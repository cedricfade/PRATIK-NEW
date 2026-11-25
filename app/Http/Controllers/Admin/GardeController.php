<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\VideCachesPublics;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GardeStoreRequest;
use App\Models\PeriodeGarde;
use App\Models\PharmacieGarde;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Écran hebdomadaire : choisir une période, cocher les pharmacies de garde
 * (filtrées par commune / nom), retirer celles qui ne le sont plus.
 */
class GardeController extends Controller
{
    use VideCachesPublics;

    public function index(Request $request): View
    {
        $jour = now()->toDateString();
        $periodes = PeriodeGarde::orderByDesc('DEBUT')->orderByDesc('ID')->limit(30)->get();

        $periode = $request->integer('periode')
            ? PeriodeGarde::find($request->integer('periode'))
            : (PeriodeGarde::courante() ?? PeriodeGarde::where('DEBUT', '>', $jour)->orderBy('DEBUT')->first() ?? $periodes->first());

        $gardes = collect();
        $dejaDeGarde = [];

        if ($periode) {
            $gardes = PharmacieGarde::query()
                ->join('entreprises as en', 'en.ID', '=', 'pharmacie_garde.ENTREPRISE_ID')
                ->leftJoin('communes as co', 'co.ID', '=', 'en.COMMUNE_ID')
                ->leftJoin('annuaires as an', 'an.ENTREPRISE_ID', '=', 'en.ID')
                ->where('pharmacie_garde.DEBUT_GARDE', $periode->DEBUT)
                ->where('pharmacie_garde.FIN_GARDE', $periode->FIN)
                ->orderBy('co.DESIGNATION')
                ->orderBy('en.TITRE')
                ->get([
                    'pharmacie_garde.ID', 'pharmacie_garde.ENTREPRISE_ID', 'en.TITRE', 'an.TEL_1', 'an.ADRESSE',
                    DB::raw("COALESCE(co.DESIGNATION, 'Sans commune') as COMMUNE"),
                ]);

            $dejaDeGarde = $gardes->pluck('ENTREPRISE_ID')->flip()->all();
        }

        // Communes ayant des pharmacies, avec leur nombre (pour le filtre).
        $communes = DB::table('thematique_annuaire as t')
            ->join('entreprises as en', 'en.ID', '=', 't.ENTREPRISE_ID')
            ->join('communes as co', 'co.ID', '=', 'en.COMMUNE_ID')
            ->where('t.SOUS_CATEGORIE_ID', config('pratik.pharmacie_sous_categorie'))
            ->groupBy('co.ID', 'co.DESIGNATION')
            ->orderBy('co.DESIGNATION')
            ->get(['co.ID', 'co.DESIGNATION', DB::raw('COUNT(DISTINCT en.ID) as total')]);

        $communeId = $request->filled('commune') ? $request->integer('commune') : null;
        $recherche = trim((string) $request->query('q'));

        $pharmacies = collect();
        if ($communeId || mb_strlen($recherche) >= 2) {
            $pharmacies = $this->pharmacies($communeId, $recherche)->limit(300)->get();
        }

        return view('admin.gardes.index', compact('periodes', 'periode', 'gardes', 'dejaDeGarde', 'communes', 'communeId', 'recherche', 'pharmacies'));
    }

    public function store(GardeStoreRequest $request): RedirectResponse
    {
        $periode = PeriodeGarde::findOrFail($request->integer('periode_id'));

        $existants = PharmacieGarde::where('DEBUT_GARDE', $periode->DEBUT)
            ->where('FIN_GARDE', $periode->FIN)
            ->whereIn('ENTREPRISE_ID', $request->input('entreprises'))
            ->pluck('ENTREPRISE_ID')
            ->all();

        $nouveaux = collect($request->input('entreprises'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->diff($existants)
            ->map(fn ($id) => [
                'ENTREPRISE_ID' => $id,
                'DATE_LOG' => now()->toDateString(),
                'DEBUT_GARDE' => $periode->DEBUT,
                'FIN_GARDE' => $periode->FIN,
                'UTILISATEUR' => $request->user()->id,
            ]);

        if ($nouveaux->isNotEmpty()) {
            PharmacieGarde::insert($nouveaux->values()->all());
        }

        $this->viderCacheGardes();

        $message = $nouveaux->count().' pharmacie(s) ajoutée(s) à la garde.';
        if ($existants) {
            $message .= ' '.count($existants).' déjà présente(s), ignorée(s).';
        }

        return back()->with('success', $message);
    }

    public function destroy(PharmacieGarde $garde): RedirectResponse
    {
        $garde->delete();
        $this->viderCacheGardes();

        return back()->with('success', 'Pharmacie retirée de la garde.');
    }

    public function vider(PeriodeGarde $periode): RedirectResponse
    {
        $total = PharmacieGarde::where('DEBUT_GARDE', $periode->DEBUT)->where('FIN_GARDE', $periode->FIN)->delete();
        $this->viderCacheGardes();

        return back()->with('success', "{$total} garde(s) supprimée(s) pour la période {$periode->libelle}.");
    }

    /** Pharmacies de l'annuaire (sous-catégorie "Pharmacies"), une ligne par entreprise. */
    private function pharmacies(?int $communeId, string $recherche)
    {
        return DB::table('thematique_annuaire as t')
            ->join('entreprises as en', 'en.ID', '=', 't.ENTREPRISE_ID')
            ->leftJoin('annuaires as an', 'an.ENTREPRISE_ID', '=', 'en.ID')
            ->leftJoin('communes as co', 'co.ID', '=', 'en.COMMUNE_ID')
            ->where('t.SOUS_CATEGORIE_ID', config('pratik.pharmacie_sous_categorie'))
            ->when($communeId, fn ($q) => $q->where('en.COMMUNE_ID', $communeId))
            ->when(mb_strlen($recherche) >= 2, fn ($q) => $q->where('en.TITRE', 'like', '%'.$recherche.'%'))
            ->groupBy('en.ID', 'en.TITRE', 'co.DESIGNATION')
            ->orderBy('en.TITRE')
            ->select([
                'en.ID', 'en.TITRE', 'co.DESIGNATION as COMMUNE',
                DB::raw('MAX(an.ADRESSE) as ADRESSE'), DB::raw('MAX(an.TEL_1) as TEL_1'),
            ]);
    }
}
