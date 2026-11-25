<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorieAnnuaire;
use App\Models\SousCategorie;
use App\Models\ThematiqueAnnuaire;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SousCategorieController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q'));

        $sousCategories = SousCategorie::query()
            ->when($request->filled('categorie'), fn ($q) => $q->where('CATEGORIE_ID', $request->integer('categorie')))
            ->when($recherche !== '', fn ($q) => $q->where('DESIGNATION', 'like', "%{$recherche}%"))
            ->with('categorie:ID,DESIGNATION,LIEN')
            ->orderBy('DESIGNATION')
            ->paginate(40)
            ->withQueryString();

        $comptes = ThematiqueAnnuaire::whereIn('SOUS_CATEGORIE_ID', $sousCategories->pluck('ID'))
            ->groupBy('SOUS_CATEGORIE_ID')
            ->get(['SOUS_CATEGORIE_ID', DB::raw('COUNT(DISTINCT ENTREPRISE_ID) as total')])
            ->pluck('total', 'SOUS_CATEGORIE_ID');

        $categories = CategorieAnnuaire::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']);

        return view('admin.annuaire.sous-categories', compact('sousCategories', 'comptes', 'categories', 'recherche'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->valider($request);

        if ($request->hasFile('pub')) {
            $data['PUBS'] = Media::store($request->file('pub'), 'pubs', 'PUB');
        }

        SousCategorie::create($data + ['DATE_LOG' => now()->toDateString(), 'UTILISATEUR' => $request->user()->name, 'ACTIVATION' => 1]);

        return back()->with('success', 'Sous-catégorie ajoutée.');
    }

    public function edit(SousCategorie $sousCategorie): View
    {
        return view('admin.annuaire.sous-categorie-edit', [
            'sous' => $sousCategorie,
            'categories' => CategorieAnnuaire::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
        ]);
    }

    public function update(Request $request, SousCategorie $sousCategorie): RedirectResponse
    {
        $data = $this->valider($request, $sousCategorie);
        $ancienne = $sousCategorie->PUBS;

        if ($request->hasFile('pub')) {
            $data['PUBS'] = Media::store($request->file('pub'), 'pubs', 'PUB');
        } elseif ($request->boolean('supprimer_pub')) {
            $data['PUBS'] = null;
        }

        $sousCategorie->update($data);

        if (array_key_exists('PUBS', $data) && $ancienne && $ancienne !== $data['PUBS'] && ! SousCategorie::where('PUBS', $ancienne)->exists()) {
            Media::delete('pubs', $ancienne);
        }

        // Les rattachements suivent la catégorie de la sous-catégorie.
        if ($sousCategorie->wasChanged('CATEGORIE_ID')) {
            ThematiqueAnnuaire::where('SOUS_CATEGORIE_ID', $sousCategorie->ID)->update(['CATEGORIE_ID' => $sousCategorie->CATEGORIE_ID]);
        }

        return redirect()->route('admin.sous-categories.index', ['categorie' => $sousCategorie->CATEGORIE_ID])->with('success', 'Sous-catégorie enregistrée.');
    }

    public function destroy(SousCategorie $sousCategorie): RedirectResponse
    {
        if ((int) $sousCategorie->ID === (int) config('pratik.pharmacie_sous_categorie')) {
            return back()->with('error', 'La rubrique « Pharmacies » est utilisée par les gardes : elle ne peut pas être supprimée.');
        }

        $total = ThematiqueAnnuaire::where('SOUS_CATEGORIE_ID', $sousCategorie->ID)->count();
        if ($total > 0) {
            return back()->with('error', "{$total} entreprise(s) sont rattachées à cette sous-catégorie : désactivez-la plutôt.");
        }

        $pub = $sousCategorie->PUBS;
        $sousCategorie->delete();
        if ($pub && ! SousCategorie::where('PUBS', $pub)->exists()) {
            Media::delete('pubs', $pub);
        }

        return back()->with('success', 'Sous-catégorie supprimée.');
    }

    private function valider(Request $request, ?SousCategorie $sous = null): array
    {
        $request->merge(['lien' => Str::slug($request->input('lien') ?: $request->input('designation'))]);

        $data = $request->validate([
            'categorie_id' => ['required', 'integer', 'exists:categorie_annuaires,ID'],
            'designation' => ['required', 'string', 'max:100'],
            'lien' => ['required', 'string', 'max:100', Rule::unique('sous_categorie', 'LIEN')->where('CATEGORIE_ID', $request->integer('categorie_id'))->ignore($sous?->ID, 'ID')],
            'descriptif' => ['nullable', 'string', 'max:5000'],
            'metas' => ['nullable', 'string', 'max:5000'],
            'lien_pub' => ['nullable', 'url', 'max:250'],
            'activation' => ['nullable', 'boolean'],
            'pub' => ['nullable', 'image', 'max:4096'],
        ]);

        return [
            'CATEGORIE_ID' => $data['categorie_id'],
            'DESIGNATION' => $data['designation'],
            'LIEN' => $data['lien'],
            'DESCRIPTIF' => $data['descriptif'] ?? null,
            'METAS' => $data['metas'] ?? null,
            'LIEN_PUB' => $data['lien_pub'] ?? null,
            'ACTIVATION' => $request->boolean('activation', true) ? 1 : 0,
        ];
    }
}
