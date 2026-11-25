<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PapierRequest;
use App\Models\Commune;
use App\Models\PapierAdministratif;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Repris de l'ancien PapierAdministratifController (même table, mêmes règles). */
class PapierController extends Controller
{
    public function index(): View
    {
        return $this->vue();
    }

    public function edit(PapierAdministratif $papier): View
    {
        return $this->vue($papier->load('pieces'));
    }

    public function store(PapierRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $papier = PapierAdministratif::create($request->donnees() + [
                'IMAGE' => $request->hasFile('image') ? Media::store($request->file('image'), 'papiers', '', 800) : null,
                'DATE_LOG' => now()->toDateString(),
                'UTILISATEUR' => $request->user()->name,
            ]);
            $this->synchroniserPieces($papier, $request->input('pieces', []));
        });

        return redirect()->route('admin.papiers.index')->with('success', 'Fiche ajoutée.');
    }

    public function update(PapierRequest $request, PapierAdministratif $papier): RedirectResponse
    {
        DB::transaction(function () use ($request, $papier) {
            $donnees = $request->donnees();
            if ($request->hasFile('image')) {
                $donnees['IMAGE'] = Media::store($request->file('image'), 'papiers', '', 800);
            }
            $papier->update($donnees); // l'ancienne image est supprimée par le modèle
            $this->synchroniserPieces($papier, $request->input('pieces', []));
        });

        return redirect()->route('admin.papiers.index')->with('success', 'Fiche modifiée.');
    }

    public function destroy(PapierAdministratif $papier): RedirectResponse
    {
        $papier->delete(); // pièces + image supprimées par le modèle

        return redirect()->route('admin.papiers.index')->with('success', 'Fiche supprimée.');
    }

    private function vue(?PapierAdministratif $edit = null): View
    {
        return view('admin.papiers.index', [
            'items' => PapierAdministratif::with(['commune:ID,DESIGNATION', 'pieces'])->orderBy('ORDRE')->orderByDesc('DATE_LOG')->orderByDesc('ID')->get(),
            'communes' => Commune::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
            'edit' => $edit,
        ]);
    }

    /** Remplace la liste des pièces à fournir (lignes vides ignorées). */
    private function synchroniserPieces(PapierAdministratif $papier, array $pieces): void
    {
        $papier->pieces()->delete();

        $rang = 0;
        foreach ($pieces as $libelle) {
            $libelle = trim((string) $libelle);
            if ($libelle !== '') {
                $papier->pieces()->create(['LIBELLE' => $libelle, 'RANG' => $rang++]);
            }
        }
    }
}
