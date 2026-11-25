<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\News;
use App\Models\SourceNewsWeb;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Articles d'actualité externes (news.CATEGORIE_ID = 2), comme l'ancien createNewsWeb. */
class NewsWebController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q'));

        $news = News::web()
            ->when($request->filled('source'), fn ($q) => $q->where('SOURCE_WEB_ID', $request->integer('source')))
            ->when($recherche !== '', fn ($q) => $q->whereHas('entreprise', fn ($e) => $e->where('TITRE', 'like', "%{$recherche}%")))
            ->with(['entreprise:ID,TITRE,LIEN', 'sourceWeb:ID,DESIGNATION'])
            ->orderByDesc('news.ID')
            ->paginate(30, ['news.ID', 'ENTREPRISE_ID', 'SOURCE_WEB_ID', 'IMAGE', 'LIEN_WEB', 'DATE_PARUTION'])
            ->withQueryString();

        return view('admin.news.web', [
            'news' => $news,
            'recherche' => $recherche,
            'sources' => SourceNewsWeb::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titre' => ['required', 'string', 'max:300'],
            'lien_web' => ['required', 'url', 'max:1000'],
            'source_web_id' => ['required', 'integer', 'exists:source_news_web,ID'],
            'date_parution' => ['required', 'date'],
            'resume' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:6144'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $entreprise = Entreprise::create([
                'TITRE' => $data['titre'],
                'TYPE_ID' => config('pratik.types.news'),
                'LIEN' => 'tmp-'.uniqid(),
                'PRIORITE' => 1,
                'COMPTEUR' => 0,
                'DATE_LOG' => now()->toDateString(),
                'ACTIVATION' => 1,
                'UTILISATEUR' => $request->user()->id,
            ]);
            $entreprise->update(['LIEN' => Entreprise::lienUnique($data['titre'], $entreprise->ID)]);

            News::create([
                'ENTREPRISE_ID' => $entreprise->ID,
                'CATEGORIE_ID' => config('pratik.news_categories.web'),
                'DATE_PARUTION' => $data['date_parution'],
                'DATE_LOG' => now()->toDateString(),
                'RESUME' => $data['resume'] ?? null,
                'LIEN_WEB' => $data['lien_web'],
                'SOURCE_WEB_ID' => $data['source_web_id'],
                'UTILISATEUR' => $request->user()->id,
                'IMAGE' => $request->hasFile('image') ? Media::store($request->file('image'), 'news', '', 1000) : null,
            ]);
        });

        return back()->with('success', 'Article ajouté.');
    }

    public function destroy(News $news): RedirectResponse
    {
        abort_unless((int) $news->CATEGORIE_ID === (int) config('pratik.news_categories.web'), 404);
        $news->supprimer();

        return back()->with('success', 'Article supprimé.');
    }
}
