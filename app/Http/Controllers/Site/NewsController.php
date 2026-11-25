<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\SourceNewsWeb;
use App\Models\VideoInfo;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class NewsController extends Controller
{
    private const TYPES = [1 => 'Quotidiens', 2 => 'Hebdomadaires', 3 => 'Mensuels'];

    /** Titrologie : les unes des journaux du jour (ou de la dernière parution disponible). */
    public function index(Request $request): View|RedirectResponse
    {
        $demandee = $request->query('date', $request->query('date_archive'));
        $date = null;

        if ($demandee) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', (string) $demandee)->toDateString();
            } catch (\Throwable) {
                return redirect()->route('news.index');
            }
        }

        $derniere = Cache::remember('news:derniere-date:'.now()->toDateString(), 600, fn () => News::titrologie()
            ->where('DATE_PARUTION', '<=', now()->toDateString())
            ->max('DATE_PARUTION'));

        $date ??= $derniere ?? now()->toDateString();

        $unes = Cache::remember('news:unes:'.$date, $date === now()->toDateString() ? 300 : 86400, fn () => News::titrologie()
            ->where('DATE_PARUTION', $date)
            ->with(['entreprise:ID,TITRE,LIEN', 'source:ID,DESIGNATION'])
            ->orderBy('TYPE_NEWS_ID')
            ->orderBy('RANG')
            ->get(['ID', 'ENTREPRISE_ID', 'SOURCE_ID', 'TYPE_NEWS_ID', 'IMAGE', 'DATE_PARUTION', 'RANG']));

        $groupes = collect(self::TYPES)
            ->map(fn ($libelle, $type) => ['libelle' => $libelle, 'unes' => $unes->where('TYPE_NEWS_ID', $type)->values()])
            ->filter(fn ($g) => $g['unes']->isNotEmpty());

        $autres = $unes->filter(fn ($u) => ! array_key_exists((int) $u->TYPE_NEWS_ID, self::TYPES));
        if ($autres->isNotEmpty()) {
            $groupes->put(0, ['libelle' => 'Autres titres', 'unes' => $autres->values()]);
        }

        $newsWeb = Cache::remember('news:web:dernieres', 600, fn () => News::web()
            ->with(['entreprise:ID,TITRE,LIEN', 'sourceWeb:ID,DESIGNATION,LOGO'])
            ->orderByDesc('ID')
            ->limit(6)
            ->get(['ID', 'ENTREPRISE_ID', 'SOURCE_WEB_ID', 'IMAGE', 'DATE_PARUTION', 'LIEN_WEB']));

        $dateCarbon = Carbon::parse($date)->locale('fr');
        $estAujourdhui = $dateCarbon->isToday();

        return view('site.news.index', compact('groupes', 'date', 'dateCarbon', 'estAujourdhui', 'newsWeb', 'derniere'));
    }

    public function une(string $lien): View
    {
        $une = News::titrologie()
            ->whereIn('ENTREPRISE_ID', fn ($q) => $q->select('ID')->from('entreprises')->where('LIEN', $lien))
            ->with(['entreprise:ID,TITRE,LIEN', 'source:ID,DESIGNATION', 'typeNews:ID,DESIGNATION'])
            ->firstOrFail();

        $memeJour = News::titrologie()
            ->where('DATE_PARUTION', $une->DATE_PARUTION)
            ->with(['entreprise:ID,TITRE,LIEN', 'source:ID,DESIGNATION'])
            ->orderBy('TYPE_NEWS_ID')
            ->orderBy('RANG')
            ->get(['ID', 'ENTREPRISE_ID', 'SOURCE_ID', 'TYPE_NEWS_ID', 'IMAGE', 'RANG']);

        $index = $memeJour->search(fn ($n) => $n->ID === $une->ID);
        $precedente = $index > 0 ? $memeJour[$index - 1] : null;
        $suivante = $index !== false && $index < $memeJour->count() - 1 ? $memeJour[$index + 1] : null;

        return view('site.news.une', compact('une', 'memeJour', 'precedente', 'suivante'));
    }

    public function web(Request $request): View
    {
        $sources = Cache::remember('news:sources-web', 3600, fn () => SourceNewsWeb::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION', 'LOGO']));
        $source = $request->filled('source') ? $sources->firstWhere('ID', (int) $request->query('source')) : null;

        $articles = News::web()
            ->when($source, fn ($q) => $q->where('SOURCE_WEB_ID', $source->ID))
            ->with(['entreprise:ID,TITRE,LIEN', 'sourceWeb:ID,DESIGNATION,LOGO'])
            ->orderByDesc('ID')
            ->paginate(18, ['ID', 'ENTREPRISE_ID', 'SOURCE_WEB_ID', 'IMAGE', 'RESUME', 'DATE_PARUTION', 'LIEN_WEB'])
            ->withQueryString();

        return view('site.news.web', compact('articles', 'sources', 'source'));
    }

    public function article(string $lien): View
    {
        // Certains liens historiques se terminent par "/", que l'URL ne conserve pas.
        $variantes = array_unique([$lien, trim($lien, '/'), trim($lien, '/').'/']);

        $article = News::web()
            ->whereIn('ENTREPRISE_ID', fn ($q) => $q->select('ID')->from('entreprises')->whereIn('LIEN', $variantes))
            ->with(['entreprise:ID,TITRE,LIEN', 'sourceWeb:ID,DESIGNATION,LOGO'])
            ->firstOrFail();

        $autres = News::web()
            ->where('ID', '!=', $article->ID)
            ->with(['entreprise:ID,TITRE,LIEN', 'sourceWeb:ID,DESIGNATION,LOGO'])
            ->orderByDesc('ID')
            ->limit(5)
            ->get(['ID', 'ENTREPRISE_ID', 'SOURCE_WEB_ID', 'IMAGE', 'DATE_PARUTION', 'LIEN_WEB']);

        return view('site.news.article', compact('article', 'autres'));
    }

    public function videos(): View
    {
        $videos = VideoInfo::orderByDesc('id')->paginate(12);

        return view('site.news.videos', compact('videos'));
    }
}
