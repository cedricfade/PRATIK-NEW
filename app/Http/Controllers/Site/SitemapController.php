<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CategorieAnnuaire;
use App\Models\Entreprise;
use App\Models\SousCategorie;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Sitemaps pour Google, découpés (max 5000 URLs par fichier) et mis en cache 12 h.
 * Les fiches sont parcourues par paquets : jamais toute la table en mémoire.
 */
class SitemapController extends Controller
{
    private const TAILLE_TRANCHE = 5000;

    private const DUREE_CACHE = 43200;

    public function index(): Response
    {
        $xml = Cache::remember('sitemap:index', self::DUREE_CACHE, function () {
            $fichiers = [
                url('/sitemaps/pages.xml'),
                url('/sitemaps/annuaire-rubriques.xml'),
                url('/sitemaps/immobilier.xml'),
                url('/sitemaps/news.xml'),
            ];

            foreach (range(0, $this->nombreTranchesAnnuaire() - 1) as $n) {
                $fichiers[] = url("/sitemaps/annuaire-{$n}.xml");
            }

            $lignes = array_map(fn ($u) => '<sitemap><loc>'.e($u).'</loc><lastmod>'.now()->toDateString().'</lastmod></sitemap>', $fichiers);

            return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
                .'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.implode('', $lignes).'</sitemapindex>';
        });

        return $this->xml($xml);
    }

    public function pages(): Response
    {
        return $this->xml(Cache::remember('sitemap:pages', self::DUREE_CACHE, function () {
            $urls = [
                [route('home'), 'daily', '1.0'],
                [route('annuaire.index'), 'weekly', '0.9'],
                [route('pharmacies.index'), 'daily', '0.9'],
                [route('news.index'), 'daily', '0.8'],
                [route('news.web'), 'daily', '0.6'],
                [route('news.videos'), 'weekly', '0.5'],
                [route('papiers.index'), 'monthly', '0.6'],
                [route('vols.index'), 'daily', '0.6'],
                [route('souscription'), 'monthly', '0.5'],
                [route('contact'), 'yearly', '0.3'],
                [route('cgu'), 'yearly', '0.1'],
                [route('confidentialite'), 'yearly', '0.1'],
                [url('/immobilier'), 'daily', '0.9'],
            ];

            $communes = DB::table('pharmacie_garde')
                ->join('entreprises', 'entreprises.ID', '=', 'pharmacie_garde.ENTREPRISE_ID')
                ->join('communes', 'communes.ID', '=', 'entreprises.COMMUNE_ID')
                ->where('pharmacie_garde.FIN_GARDE', '>=', now()->subMonths(3)->toDateString())
                ->distinct()
                ->pluck('communes.LIEN');

            foreach ($communes as $lien) {
                $urls[] = [route('pharmacies.commune', $lien), 'daily', '0.7'];
            }

            return $this->urlset($urls);
        }));
    }

    public function rubriques(): Response
    {
        return $this->xml(Cache::remember('sitemap:rubriques', self::DUREE_CACHE, function () {
            $urls = [];
            $categories = CategorieAnnuaire::whereNotNull('LIEN')->where('LIEN', '!=', '')->get(['ID', 'LIEN'])->keyBy('ID');

            foreach ($categories as $c) {
                $urls[] = [route('annuaire.categorie', $c->LIEN), 'weekly', '0.7'];
            }

            SousCategorie::where('ACTIVATION', 1)->where('LIEN', '!=', '')->get(['CATEGORIE_ID', 'LIEN'])
                ->each(function ($s) use ($categories, &$urls) {
                    if ($cat = $categories[$s->CATEGORIE_ID] ?? null) {
                        $urls[] = [route('annuaire.sous-categorie', [$cat->LIEN, $s->LIEN]), 'weekly', '0.6'];
                    }
                });

            return $this->urlset($urls);
        }));
    }

    public function annuaire(int $tranche): Response
    {
        abort_if($tranche < 0 || $tranche >= $this->nombreTranchesAnnuaire(), 404);

        return $this->xml(Cache::remember("sitemap:annuaire:{$tranche}", self::DUREE_CACHE, function () use ($tranche) {
            $urls = [];

            Entreprise::query()
                ->fichesAnnuaire()
                ->where('entreprises.LIEN', '!=', '')
                ->whereIn('entreprises.ID', fn ($q) => $q->select('ENTREPRISE_ID')->from('annuaires'))
                ->orderBy('entreprises.ID')
                ->offset($tranche * self::TAILLE_TRANCHE)
                ->limit(self::TAILLE_TRANCHE)
                ->select(['entreprises.ID', 'entreprises.LIEN', 'entreprises.DATE_LOG'])
                ->cursor()
                ->each(function ($e) use (&$urls) {
                    $urls[] = [route('annuaire.fiche', $e->LIEN), 'monthly', '0.5', $this->dateValide($e->DATE_LOG)];
                });

            return $this->urlset($urls);
        }));
    }

    public function immobilier(): Response
    {
        return $this->xml(Cache::remember('sitemap:immobilier', self::DUREE_CACHE, function () {
            $urls = [];

            // Query builder brut : l'accesseur Immobilier::lien masquerait la colonne jointe.
            DB::table('immobilier')
                ->where('immobilier.VALIDATION', 'OUI')
                ->join('entreprises', 'entreprises.ID', '=', 'immobilier.ENTREPRISE_ID')
                ->where('entreprises.LIEN', '!=', '')
                ->orderByDesc('immobilier.ID')
                ->select(['entreprises.LIEN', 'immobilier.DATE_LOG'])
                ->cursor()
                ->each(function ($b) use (&$urls) {
                    $urls[] = [url('/immobilier/proprietes-'.$b->LIEN), 'weekly', '0.6', $this->dateValide($b->DATE_LOG)];
                });

            return $this->urlset($urls);
        }));
    }

    public function news(): Response
    {
        return $this->xml(Cache::remember('sitemap:news', 3600 * 3, function () {
            $urls = [];

            DB::table('news')
                ->join('entreprises', 'entreprises.ID', '=', 'news.ENTREPRISE_ID')
                ->whereIn('news.CATEGORIE_ID', [config('pratik.news_categories.titrologie'), config('pratik.news_categories.web')])
                ->where('news.DATE_PARUTION', '>=', now()->subDays(60)->toDateString())
                ->where('entreprises.LIEN', '!=', '')
                ->orderByDesc('news.ID')
                ->select(['news.CATEGORIE_ID', 'news.DATE_PARUTION', 'entreprises.LIEN'])
                ->cursor()
                ->each(function ($n) use (&$urls) {
                    $chemin = (int) $n->CATEGORIE_ID === (int) config('pratik.news_categories.web') ? '/news-web/' : '/news/';
                    $urls[] = [url($chemin.trim($n->LIEN, '/')), 'monthly', '0.4', $this->dateValide($n->DATE_PARUTION)];
                });

            return $this->urlset($urls);
        }));
    }

    public function robots(): Response
    {
        $contenu = app()->isProduction()
            ? implode("\n", [
                'User-agent: *',
                'Disallow: /admin',
                'Disallow: /immobilier/espace-pro',
                'Disallow: /connexion',
                'Disallow: /immobilier/connexion',
                'Disallow: /inscription',
                'Disallow: /mon-compte',
                'Disallow: /mot-de-passe-oublie',
                'Disallow: /stats/',
                'Disallow: /api/',
                '',
                'Sitemap: '.url('/sitemap.xml'),
            ])
            // Hors production (préproduction, local) : rien ne doit être indexé.
            : "User-agent: *\nDisallow: /";

        return response($contenu."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function nombreTranchesAnnuaire(): int
    {
        $total = Cache::remember('sitemap:annuaire:total', self::DUREE_CACHE, fn () => Entreprise::query()
            ->fichesAnnuaire()
            ->where('entreprises.LIEN', '!=', '')
            ->whereIn('entreprises.ID', fn ($q) => $q->select('ENTREPRISE_ID')->from('annuaires'))
            ->count());

        return max(1, (int) ceil($total / self::TAILLE_TRANCHE));
    }

    /** @param  list<array{0:string,1:string,2:string,3?:?string}>  $urls */
    private function urlset(array $urls): string
    {
        $corps = '';
        foreach ($urls as $u) {
            $corps .= '<url><loc>'.e($u[0]).'</loc>'
                .(! empty($u[3]) ? '<lastmod>'.$u[3].'</lastmod>' : '')
                .'<changefreq>'.$u[1].'</changefreq><priority>'.$u[2].'</priority></url>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$corps.'</urlset>';
    }

    private function dateValide(mixed $date): ?string
    {
        $date = (string) $date;

        return preg_match('/^(19|20)\d{2}-\d{2}-\d{2}$/', $date) && $date !== '0000-00-00' ? $date : null;
    }

    private function xml(string $contenu): Response
    {
        return response($contenu, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
