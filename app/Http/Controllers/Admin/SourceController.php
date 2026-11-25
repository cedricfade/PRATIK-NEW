<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\SourceNews;
use App\Models\SourceNewsWeb;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Journaux (titrologie) et sites d'information (news web). */
class SourceController extends Controller
{
    public function index(): View
    {
        return view('admin.news.sources', [
            'presse' => SourceNews::orderBy('DESIGNATION')->get(),
            'web' => SourceNewsWeb::orderBy('DESIGNATION')->get(),
            'comptesPresse' => News::groupBy('SOURCE_ID')->whereNotNull('SOURCE_ID')->get(['SOURCE_ID', DB::raw('COUNT(*) as total')])->pluck('total', 'SOURCE_ID'),
            'comptesWeb' => News::groupBy('SOURCE_WEB_ID')->whereNotNull('SOURCE_WEB_ID')->get(['SOURCE_WEB_ID', DB::raw('COUNT(*) as total')])->pluck('total', 'SOURCE_WEB_ID'),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $data = $this->valider($request);

        if ($type === 'presse') {
            SourceNews::create(['DESIGNATION' => $data['designation'], 'SLUG' => Str::slug($data['designation']), 'ACTIVATION' => 1]);
        } else {
            SourceNewsWeb::create([
                'DESIGNATION' => $data['designation'],
                'LOGO' => $request->hasFile('logo') ? Media::store($request->file('logo'), 'news_sources', '', 400) : null,
                'DATE_LOG' => now()->toDateString(),
                'UTILISATEUR' => $request->user()->id,
            ]);
        }

        return back()->with('success', 'Source ajoutée.');
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $data = $this->valider($request);

        if ($type === 'presse') {
            SourceNews::findOrFail($id)->update([
                'DESIGNATION' => $data['designation'],
                'ACTIVATION' => $request->boolean('activation') ? 1 : 0,
            ]);
        } else {
            $source = SourceNewsWeb::findOrFail($id);
            $maj = ['DESIGNATION' => $data['designation']];
            if ($request->hasFile('logo')) {
                $maj['LOGO'] = Media::store($request->file('logo'), 'news_sources', '', 400);
            }
            $source->update($maj); // l'ancien logo est supprimé par le modèle
        }

        return back()->with('success', 'Source modifiée.');
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $colonne = $type === 'presse' ? 'SOURCE_ID' : 'SOURCE_WEB_ID';
        $total = News::where($colonne, $id)->count();

        if ($total > 0) {
            return back()->with('error', "{$total} publication(s) utilisent cette source : elle ne peut pas être supprimée.");
        }

        ($type === 'presse' ? SourceNews::findOrFail($id) : SourceNewsWeb::findOrFail($id))->delete();

        return back()->with('success', 'Source supprimée.');
    }

    private function valider(Request $request): array
    {
        return $request->validate([
            'designation' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
