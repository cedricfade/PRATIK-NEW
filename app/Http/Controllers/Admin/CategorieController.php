<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorieAnnuaire;
use App\Models\ThematiqueAnnuaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategorieController extends Controller
{
    public function index(): View
    {
        $categories = CategorieAnnuaire::orderBy('DESIGNATION')
            ->withCount('sousCategories')
            ->get();

        $entreprises = ThematiqueAnnuaire::groupBy('CATEGORIE_ID')
            ->get(['CATEGORIE_ID', DB::raw('COUNT(DISTINCT ENTREPRISE_ID) as total')])
            ->pluck('total', 'CATEGORIE_ID');

        return view('admin.annuaire.categories', compact('categories', 'entreprises'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->valider($request);

        CategorieAnnuaire::create($data + ['DATE_LOG' => now()->toDateString(), 'USER' => Str::limit($request->user()->name, 25, '')]);

        return back()->with('success', 'Catégorie ajoutée.');
    }

    public function update(Request $request, CategorieAnnuaire $categorie): RedirectResponse
    {
        $categorie->update($this->valider($request, $categorie));

        return back()->with('success', 'Catégorie modifiée.');
    }

    public function destroy(CategorieAnnuaire $categorie): RedirectResponse
    {
        if ($categorie->sousCategories()->exists() || ThematiqueAnnuaire::where('CATEGORIE_ID', $categorie->ID)->exists()) {
            return back()->with('error', 'Cette catégorie contient des sous-catégories ou des entreprises : déplacez-les avant de la supprimer.');
        }

        $categorie->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }

    private function valider(Request $request, ?CategorieAnnuaire $categorie = null): array
    {
        $request->merge(['lien' => Str::slug($request->input('lien') ?: $request->input('designation'))]);

        $data = $request->validate([
            'designation' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'mots_cles' => ['nullable', 'string', 'max:250'],
            'lien' => ['required', 'string', 'max:100', Rule::unique('categorie_annuaires', 'LIEN')->ignore($categorie?->ID, 'ID')],
        ]);

        return ['DESIGNATION' => $data['designation'], 'DESCRIPTION' => $data['description'] ?? null, 'MOTS_CLES' => $data['mots_cles'] ?? null, 'LIEN' => $data['lien']];
    }
}
