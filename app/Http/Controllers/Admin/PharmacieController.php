<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PharmacieController extends Controller
{
    public function index(Request $request): View
    {
        $jour = now()->toDateString();
        $recherche = trim((string) $request->query('q'));

        $pharmacies = DB::table('thematique_annuaire as t')
            ->join('entreprises as en', 'en.ID', '=', 't.ENTREPRISE_ID')
            ->leftJoin('annuaires as an', 'an.ENTREPRISE_ID', '=', 'en.ID')
            ->leftJoin('communes as co', 'co.ID', '=', 'en.COMMUNE_ID')
            ->where('t.SOUS_CATEGORIE_ID', config('pratik.pharmacie_sous_categorie'))
            ->when($request->filled('commune'), fn ($q) => $q->where('en.COMMUNE_ID', $request->integer('commune')))
            ->when($recherche !== '', fn ($q) => $q->where('en.TITRE', 'like', '%'.$recherche.'%'))
            ->groupBy('en.ID', 'en.TITRE', 'en.LIEN', 'co.DESIGNATION')
            ->orderBy('en.TITRE')
            ->select([
                'en.ID', 'en.TITRE', 'en.LIEN', 'co.DESIGNATION as COMMUNE',
                DB::raw('MAX(an.ADRESSE) as ADRESSE'), DB::raw('MAX(an.TEL_1) as TEL_1'), DB::raw('MAX(an.CEL_1) as CEL_1'),
                DB::raw('MAX(an.LATITUDE) as LATITUDE'),
                DB::raw("EXISTS(SELECT 1 FROM pharmacie_garde pg WHERE pg.ENTREPRISE_ID = en.ID AND pg.DEBUT_GARDE <= '{$jour}' AND pg.FIN_GARDE >= '{$jour}') as DE_GARDE"),
            ])
            ->paginate(30)
            ->withQueryString();

        $communes = Commune::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']);

        return view('admin.gardes.pharmacies', compact('pharmacies', 'communes', 'recherche'));
    }
}
