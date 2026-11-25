<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\VideCachesPublics;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TitrologieRequest;
use App\Models\Entreprise;
use App\Models\News;
use App\Models\SourceNews;
use App\Models\TypeNews;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Unes des journaux du jour (news.CATEGORIE_ID = 3), comme l'ancien createNews. */
class TitrologieController extends Controller
{
    use VideCachesPublics;

    public function index(Request $request): View
    {
        $date = $request->date('date')?->toDateString() ?? now()->toDateString();

        $unes = News::titrologie()
            ->where('DATE_PARUTION', $date)
            ->with(['entreprise:ID,TITRE,LIEN', 'source:ID,DESIGNATION', 'typeNews:ID,DESIGNATION'])
            ->orderBy('TYPE_NEWS_ID')
            ->orderBy('RANG')
            ->get();

        // Derniers jours publiés, pour naviguer rapidement.
        $jours = News::titrologie()
            ->where('DATE_PARUTION', '>=', now()->subDays(14)->toDateString())
            ->groupBy('DATE_PARUTION')
            ->orderByDesc('DATE_PARUTION')
            ->get(['DATE_PARUTION', DB::raw('COUNT(*) as total')]);

        return view('admin.news.titrologie', [
            'date' => $date,
            'unes' => $unes,
            'jours' => $jours,
            'sources' => SourceNews::where(fn ($q) => $q->where('ACTIVATION', 1)->orWhereNull('ACTIVATION'))->orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
            'typesNews' => TypeNews::orderBy('ID')->get(['ID', 'DESIGNATION']),
        ]);
    }

    public function store(TitrologieRequest $request): RedirectResponse
    {
        $date = Carbon::parse($request->input('date_parution'))->toDateString();
        $sources = SourceNews::whereIn('ID', collect($request->input('unes'))->pluck('source_id'))->get()->keyBy('ID');
        $utilisateur = $request->user()->id;
        $total = 0;

        DB::transaction(function () use ($request, $date, $sources, $utilisateur, &$total) {
            foreach ($request->input('unes') as $i => $ligne) {
                $source = $sources[$ligne['source_id']];
                $fichier = Media::store($request->file("unes.$i.image"), 'news', '', 1400);

                $entreprise = Entreprise::create([
                    'TYPE_ID' => config('pratik.types.news'),
                    'TITRE' => $source->DESIGNATION.' '.$date,
                    'LIEN' => $this->lienUnique(($source->SLUG ?: Str::slug($source->DESIGNATION)).'-'.$date),
                    'COMPTEUR' => 0,
                    'PRIORITE' => 1,
                    'DATE_LOG' => now()->toDateString(),
                    'UTILISATEUR' => $utilisateur,
                    'ACTIVATION' => 1,
                ]);

                News::create([
                    'ENTREPRISE_ID' => $entreprise->ID,
                    'TYPE_NEWS_ID' => $ligne['type_news_id'],
                    'SOURCE_ID' => $source->ID,
                    'RANG' => $ligne['rang'] ?? null,
                    'IMAGE' => $fichier,
                    'NUMERO_PARUTION' => $ligne['numero'] ?? null,
                    'DATE_PARUTION' => $date,
                    'UTILISATEUR' => $utilisateur,
                    'CATEGORIE_ID' => config('pratik.news_categories.titrologie'),
                    'DATE_LOG' => now()->toDateString(),
                ]);
                $total++;
            }
        });

        $this->viderCacheAccueil();

        return redirect()->route('admin.titrologie.index', ['date' => $date])->with('success', "{$total} une(s) publiée(s) pour le ".Carbon::parse($date)->format('d/m/Y').'.');
    }

    public function destroy(News $news): RedirectResponse
    {
        abort_unless((int) $news->CATEGORIE_ID === (int) config('pratik.news_categories.titrologie'), 404);
        $date = $news->DATE_PARUTION;
        $news->supprimer(); // fiche + image

        $this->viderCacheAccueil();

        return redirect()->route('admin.titrologie.index', ['date' => $date])->with('success', 'Une supprimée.');
    }

    private function lienUnique(string $base): string
    {
        $lien = $base;
        $i = 1;
        while (Entreprise::where('LIEN', $lien)->exists()) {
            $lien = $base.'-'.(++$i);
        }

        return $lien;
    }
}
