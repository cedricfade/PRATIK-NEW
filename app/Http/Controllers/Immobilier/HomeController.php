<?php

namespace App\Http\Controllers\Immobilier;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Immobilier\Support\Annonceurs;
use App\Http\Controllers\Immobilier\Support\RechercheBiens;
use App\Models\CategorieImmobilier;
use App\Models\Commune;
use App\Models\Immobilier;
use App\Support\Hero;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $relations = [
            'entreprise:ID,TITRE,LIEN,COMMUNE_ID',
            'entreprise.commune:ID,DESIGNATION,LIEN',
            'categorie:ID,DESIGNATION',
        ];

        // Biens mis en avant à côté de la recherche : 3 tirés au hasard à chaque visite
        // dans un lot de 30 biens avec une vraie photo (FLASH et prestige en priorité).
        $aLaUne = Cache::remember('immo:home:une', 600, fn () => Immobilier::publie()
            ->whereNotNull('ILLUSTATION')
            ->whereNotIn('ILLUSTATION', ['', Immobilier::IMAGE_DEFAUT])
            ->with($relations)
            ->orderByRaw("(FLASH = 'OUI' OR LUXE = 'OUI') desc")
            ->orderByDesc('ID')
            ->limit(30)
            ->get())
            ->shuffle()
            ->take(3)
            ->values();

        $ventes = Cache::remember('immo:home:ventes', 600, fn () => Immobilier::publie()->vente()
            ->with($relations)->orderByDesc('ID')->limit(8)->get());

        $locations = Cache::remember('immo:home:locations', 600, fn () => Immobilier::publie()->location()
            ->with($relations)->orderByDesc('ID')->limit(8)->get());

        $marqueurs = Cache::remember('immo:home:carte', 1800, fn () => RechercheBiens::marqueurs(
            Immobilier::publie()
                ->whereNotNull('LATITUDE')->where('LATITUDE', '!=', '')
                ->whereNotNull('LONGITUDE')->where('LONGITUDE', '!=', '')
                ->with('entreprise:ID,TITRE,LIEN')
                ->orderByDesc('ID')
                ->limit(300)
                ->get(['ID', 'ENTREPRISE_ID', 'LATITUDE', 'LONGITUDE', 'PRIX', 'TYPE_IMMOBILIER_ID', 'ILLUSTATION'])
        ));

        $compteurs = Cache::remember('immo:home:compteurs', 1800, fn () => [
            'ventes' => Immobilier::publie()->vente()->count(),
            'locations' => Immobilier::publie()->location()->count(),
        ]);

        $annonceurs = collect(Annonceurs::liste('tous'))
            ->filter(fn ($a) => $a['logo_url'])
            ->take(12)
            ->values();

        return view('immobilier.home', [
            'hero' => Hero::pour('immobilier', $compteurs),
            'aLaUne' => $aLaUne,
            'ventes' => $ventes,
            'locations' => $locations,
            'marqueurs' => $marqueurs,
            'compteurs' => $compteurs,
            'annonceurs' => $annonceurs,
            'categories' => self::categories(),
            'communes' => self::communes(),
        ]);
    }

    public static function categories()
    {
        return Cache::remember('immo:categories', 86400, fn () => CategorieImmobilier::query()
            ->where(fn ($q) => $q->where('ACTIVATION', 1)->orWhereNull('ACTIVATION'))
            ->orderBy('DESIGNATION')
            ->get(['ID', 'DESIGNATION']));
    }

    public static function communes()
    {
        return Cache::remember('communes:liste', 86400, fn () => Commune::where('DESIGNATION', '!=', '')
            ->orderBy('DESIGNATION')
            ->get(['ID', 'DESIGNATION', 'LIEN']));
    }
}
