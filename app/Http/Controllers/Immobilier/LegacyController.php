<?php

namespace App\Http\Controllers\Immobilier;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Redirections 301 des anciennes recherches immobilières (pratik-ci.com v1)
 * vers les nouvelles listes filtrées, en conservant les critères.
 */
class LegacyController extends Controller
{
    /**
     * @param  string  $cible  achats|locations|luxe
     * @param  array<string, mixed>  $forces  filtres imposés par l'ancienne URL
     */
    public function recherche(Request $request, string $cible = 'achats', array $forces = []): RedirectResponse
    {
        // promoteur_id / agence_id : l'ancienne recherche visait les biens d'un annonceur.
        $annonceur = $request->query('promoteur_id') ?: $request->query('agence_id');
        if ($annonceur && ($lien = Entreprise::whereKey(User::whereKey((int) $annonceur)->value('entreprise_id'))->value('LIEN'))) {
            return redirect()->route('immo.annonceur', $lien, 301);
        }

        $q = $request->query();
        $filtres = array_filter([
            'categorie' => $q['categorie'] ?? null,
            'commune' => $q['commune'] ?? $q['localisation'] ?? null,
            'prix_min' => $q['prix_min'] ?? null,
            'prix_max' => $q['prix_max'] ?? $q['prix'] ?? null,
            'chambres' => $q['chambres'] ?? $q['chambre'] ?? null,
        ], fn ($v) => is_scalar($v) && preg_replace('/\D+/', '', (string) $v) !== '' && (int) preg_replace('/\D+/', '', (string) $v) > 0);

        $filtres = array_map(fn ($v) => (int) preg_replace('/\D+/', '', (string) $v), $filtres) + $forces;

        return redirect()->route(match ($cible) {
            'locations' => 'immo.locations',
            'luxe' => 'immo.luxe',
            default => 'immo.achats',
        }, $filtres, 301);
    }

    public function achats(Request $request): RedirectResponse
    {
        return $this->recherche($request, 'achats');
    }

    public function locations(Request $request): RedirectResponse
    {
        return $this->recherche($request, 'locations');
    }

    public function terrains(Request $request): RedirectResponse
    {
        return $this->recherche($request, 'achats', ['categorie' => 7]);
    }

    public function promoteurs(Request $request): RedirectResponse
    {
        return $this->recherche($request, 'achats', ['type' => 3]);
    }

    public function luxe(Request $request): RedirectResponse
    {
        return $this->recherche($request, 'luxe');
    }

    /** Anciennes sous-pages "immobilier de luxe" dédiées à un promoteur. */
    public function pageLuxe(string $lien): RedirectResponse
    {
        $annonceurs = [
            'moolio' => 'moolio',
            'sci-dekam' => 'sci-dekam',
            'galerie-les-residences-mangroves' => 'moolio',
        ];

        $cible = $annonceurs[$lien] ?? $lien;

        if (Entreprise::where('LIEN', $cible)->whereIn('ID', User::whereNotNull('entreprise_id')->select('entreprise_id'))->exists()) {
            return redirect()->route('immo.annonceur', $cible, 301);
        }

        return redirect()->route('immo.luxe', [], 301);
    }
}
