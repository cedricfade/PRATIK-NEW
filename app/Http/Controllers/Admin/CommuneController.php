<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Entreprise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommuneController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q'));

        $communes = Commune::query()
            ->when($recherche !== '', fn ($q) => $q->where('DESIGNATION', 'like', "%{$recherche}%"))
            ->orderBy('DESIGNATION')
            ->paginate(50)
            ->withQueryString();

        $comptes = Entreprise::whereIn('COMMUNE_ID', $communes->pluck('ID'))
            ->groupBy('COMMUNE_ID')
            ->get(['COMMUNE_ID', DB::raw('COUNT(*) as total')])
            ->pluck('total', 'COMMUNE_ID');

        return view('admin.annuaire.communes', compact('communes', 'comptes', 'recherche'));
    }

    public function store(Request $request): RedirectResponse
    {
        Commune::create($this->valider($request));
        Cache::forget('communes:liste');

        return back()->with('success', 'Commune ajoutée.');
    }

    public function update(Request $request, Commune $commune): RedirectResponse
    {
        $commune->update($this->valider($request, $commune));
        Cache::forget('communes:liste');

        return back()->with('success', 'Commune modifiée.');
    }

    public function destroy(Commune $commune): RedirectResponse
    {
        $total = Entreprise::where('COMMUNE_ID', $commune->ID)->count();
        if ($total > 0) {
            return back()->with('error', "{$total} fiche(s) utilisent cette commune : elle ne peut pas être supprimée.");
        }

        $commune->delete();
        Cache::forget('communes:liste');

        return back()->with('success', 'Commune supprimée.');
    }

    private function valider(Request $request, ?Commune $commune = null): array
    {
        $request->merge(['lien' => Str::slug($request->input('lien') ?: $request->input('designation'))]);

        $data = $request->validate([
            'designation' => ['required', 'string', 'max:100'],
            'lien' => ['required', 'string', 'max:100', Rule::unique('communes', 'LIEN')->ignore($commune?->ID, 'ID')],
        ]);

        return ['DESIGNATION' => $data['designation'], 'LIEN' => $data['lien']];
    }
}
