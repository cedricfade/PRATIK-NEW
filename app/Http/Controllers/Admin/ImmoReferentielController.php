<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorieImmobilier;
use App\Models\Immobilier;
use App\Models\TypeImmobilier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Catégories (appartement, villa…) et types (agence location, promoteur…) de biens. */
class ImmoReferentielController extends Controller
{
    public function index(): View
    {
        $parCategorie = Immobilier::groupBy('CATEGORIE_IMMOBILIER_ID')->get(['CATEGORIE_IMMOBILIER_ID', DB::raw('COUNT(*) as total')])->pluck('total', 'CATEGORIE_IMMOBILIER_ID');
        $parType = Immobilier::groupBy('TYPE_IMMOBILIER_ID')->get(['TYPE_IMMOBILIER_ID', DB::raw('COUNT(*) as total')])->pluck('total', 'TYPE_IMMOBILIER_ID');

        return view('admin.immobilier.referentiels', [
            'categories' => CategorieImmobilier::orderBy('DESIGNATION')->get(),
            'types' => TypeImmobilier::orderBy('ID')->get(),
            'parCategorie' => $parCategorie,
            'parType' => $parType,
        ]);
    }

    public function store(Request $request, string $table): RedirectResponse
    {
        $data = $this->valider($request);
        $modele = $this->modele($table);

        $modele::create($table === 'categories'
            ? $data + ['DATE_LOG' => now()->toDateString(), 'USER' => $request->user()->name]
            : $data);

        return back()->with('success', 'Élément ajouté.');
    }

    public function update(Request $request, string $table, int $id): RedirectResponse
    {
        $this->modele($table)::findOrFail($id)->update($this->valider($request));

        return back()->with('success', 'Élément modifié.');
    }

    public function destroy(string $table, int $id): RedirectResponse
    {
        $element = $this->modele($table)::findOrFail($id);
        $colonne = $table === 'categories' ? 'CATEGORIE_IMMOBILIER_ID' : 'TYPE_IMMOBILIER_ID';
        $total = Immobilier::where($colonne, $id)->count();

        if ($total > 0) {
            return back()->with('error', "{$total} bien(s) utilisent cet élément : désactivez-le plutôt.");
        }

        $element->delete();

        return back()->with('success', 'Élément supprimé.');
    }

    private function valider(Request $request): array
    {
        $data = $request->validate([
            'designation' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'activation' => ['nullable', 'boolean'],
        ]);

        return array_filter([
            'DESIGNATION' => $data['designation'],
            'DESCRIPTION' => $data['description'] ?? null,
            'ACTIVATION' => $request->boolean('activation', true) ? 1 : 0,
        ], fn ($v, $k) => $k !== 'DESCRIPTION' || $request->has('description'), ARRAY_FILTER_USE_BOTH);
    }

    /** @return class-string<\Illuminate\Database\Eloquent\Model> */
    private function modele(string $table): string
    {
        return $table === 'categories' ? CategorieImmobilier::class : TypeImmobilier::class;
    }
}
