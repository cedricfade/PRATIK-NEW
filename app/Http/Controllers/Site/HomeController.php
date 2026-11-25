<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\News;
use App\Models\PharmacieGarde;
use App\Support\Hero;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $aujourdhui = now()->toDateString();

        // Tous les blocs sont mis en cache : la page d'accueil reste rapide même avec 100 000 fiches.
        $partenaires = Cache::remember('home:partenaires', 3600, fn () => Entreprise::query()
            ->fichesAnnuaire()
            ->whereHas('annuaire', fn ($q) => $q->where('VIP', 1)->whereNotNull('LOGO')->where('LOGO', '!=', '')->whereRaw('LOGO NOT REGEXP ?', ['^(logo_entrprise_default|default-logo-flou[0-9]*|default)\\.']))
            ->with(['annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1,CLIENT,VIP', 'commune:ID,DESIGNATION'])
            ->inRandomOrder()
            ->limit(12)
            ->get(['ID', 'TITRE', 'LIEN', 'COMMUNE_ID', 'ACTIVITE']));

        $gardesParCommune = Cache::remember('home:gardes:'.$aujourdhui, 600, fn () => PharmacieGarde::enCours($aujourdhui)
            ->join('entreprises', 'entreprises.ID', '=', 'pharmacie_garde.ENTREPRISE_ID')
            ->join('communes', 'communes.ID', '=', 'entreprises.COMMUNE_ID')
            ->groupBy('communes.ID', 'communes.DESIGNATION', 'communes.LIEN')
            ->orderBy('communes.DESIGNATION')
            ->get([
                'communes.ID',
                'communes.DESIGNATION',
                'communes.LIEN',
                DB::raw('COUNT(DISTINCT pharmacie_garde.ENTREPRISE_ID) as total'),
            ]));

        $unes = Cache::remember('home:unes:'.$aujourdhui, 900, function () use ($aujourdhui) {
            $date = News::titrologie()->where('DATE_PARUTION', '<=', $aujourdhui)->max('DATE_PARUTION');

            return News::titrologie()
                ->where('DATE_PARUTION', $date)
                ->with(['entreprise:ID,TITRE,LIEN', 'source:ID,DESIGNATION'])
                ->orderBy('TYPE_NEWS_ID')
                ->orderBy('RANG')
                ->limit(14)
                ->get(['ID', 'ENTREPRISE_ID', 'SOURCE_ID', 'TYPE_NEWS_ID', 'IMAGE', 'DATE_PARUTION', 'RANG']);
        });

        $biens = Cache::remember('home:biens', 900, fn () => Immobilier::publie()
            ->with(['entreprise:ID,TITRE,LIEN,COMMUNE_ID', 'entreprise.commune:ID,DESIGNATION', 'categorie:ID,DESIGNATION'])
            ->orderByDesc('ID')
            ->limit(4)
            ->get());

        $chiffres = Cache::remember('home:chiffres', 21600, fn () => [
            'entreprises' => Entreprise::fichesAnnuaire()->count(),
            'biens' => Immobilier::publie()->count(),
            'pharmacies' => DB::table('thematique_annuaire')->where('SOUS_CATEGORIE_ID', config('pratik.pharmacie_sous_categorie'))->distinct()->count('ENTREPRISE_ID'),
            'unes' => News::titrologie()->count(),
        ]);

        $communes = Cache::remember('communes:liste', 86400, fn () => Commune::where('DESIGNATION', '!=', '')->orderBy('DESIGNATION')->get(['ID', 'DESIGNATION', 'LIEN']));

        $hero = Hero::pour('accueil', $chiffres);
        $chiffresHero = Hero::chiffres();

        return view('site.home', compact('partenaires', 'gardesParCommune', 'unes', 'biens', 'chiffres', 'communes', 'hero', 'chiffresHero'));
    }
}
