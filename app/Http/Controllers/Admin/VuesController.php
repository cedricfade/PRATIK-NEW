<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorieAnnuaire;
use App\Models\Entreprise;
use App\Models\User;
use App\Support\AugmentationVues;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin › Vues : augmenter les vues affichées (annuaire, VIP, immobilier, au cas par cas). */
class VuesController extends Controller
{
    public function index(Request $request): View
    {
        $resumes = collect(['annuaire_tous', 'annuaire_vip', 'immo_biens', 'immo_vedette', 'immo_agences'])
            ->mapWithKeys(fn ($p) => [$p => AugmentationVues::resume($p)]);

        // Recherche d'une fiche précise (entreprise de l'annuaire ou annonce immobilière).
        $q = trim((string) $request->query('q'));
        $resultats = collect();
        if (ctype_digit($q) || mb_strlen($q) >= 2) {
            $resultats = Entreprise::query()
                ->whereIn('TYPE_ID', [config('pratik.types.annuaire'), config('pratik.types.immobilier')])
                ->where(fn ($w) => ctype_digit($q) ? $w->whereKey((int) $q) : $w->recherche($q))
                ->with('commune:ID,DESIGNATION')
                ->orderByDesc('COMPTEUR')
                ->limit(25)
                ->get(['ID', 'TITRE', 'LIEN', 'TYPE_ID', 'COMMUNE_ID', 'COMPTEUR', 'VUES_BONUS']);
        }

        return view('admin.vues.index', [
            'perimetres' => AugmentationVues::PERIMETRES,
            'resumes' => $resumes,
            'categories' => CategorieAnnuaire::orderBy('DESIGNATION')->get(['ID', 'DESIGNATION']),
            'annonceurs' => User::where('role_id', config('pratik.roles.immobilier'))->orderBy('name')->get(['id', 'name']),
            'q' => $q,
            'resultats' => $resultats,
            'historique' => Schema::hasTable('historique_vues')
                ? DB::table('historique_vues')->leftJoin('users', 'users.id', '=', 'historique_vues.user_id')
                    ->orderByDesc('historique_vues.id')->limit(20)
                    ->get(['historique_vues.*', 'users.name as auteur'])
                : collect(),
        ]);
    }

    public function ajouter(Request $request): RedirectResponse
    {
        $data = $this->valider($request, true);

        $fiches = AugmentationVues::ajouter($data['perimetre'], $data['minimum'], $data['maximum'], $data);
        $this->historiser($request, $data, 'ajout', $fiches);

        $plage = $data['minimum'] === $data['maximum'] ? '+'.$data['minimum'] : 'entre +'.$data['minimum'].' et +'.$data['maximum'];

        return back()->with('success', "Vues ajoutées ({$plage}) sur ".number_format($fiches, 0, ',', ' ').' fiche(s).');
    }

    public function remettreAZero(Request $request): RedirectResponse
    {
        $data = $this->valider($request, false);

        $fiches = AugmentationVues::remettreAZero($data['perimetre'], $data);
        $this->historiser($request, $data + ['minimum' => 0, 'maximum' => 0], 'remise_a_zero', $fiches);

        return back()->with('success', 'Vues ajoutées remises à zéro sur '.number_format($fiches, 0, ',', ' ').' fiche(s). Les vues réelles sont intactes.');
    }

    private function valider(Request $request, bool $avecNombres): array
    {
        $data = $request->validate([
            'perimetre' => ['required', Rule::in(array_keys(AugmentationVues::PERIMETRES))],
            'categorie' => ['nullable', 'required_if:perimetre,annuaire_categorie', 'integer', 'exists:categorie_annuaires,ID'],
            'annonceur' => ['nullable', 'required_if:perimetre,immo_annonceur', 'integer', 'exists:users,id'],
            'fiche' => ['nullable', 'required_if:perimetre,fiche', 'integer', 'exists:entreprises,ID'],
            'minimum' => [$avecNombres ? 'required' : 'nullable', 'integer', 'min:1', 'max:100000'],
            'maximum' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ], [
            'categorie.required_if' => 'Choisissez une catégorie.',
            'annonceur.required_if' => 'Choisissez un annonceur.',
        ], ['minimum' => 'nombre de vues', 'maximum' => 'maximum']);

        $data['minimum'] = (int) ($data['minimum'] ?? 0);
        $data['maximum'] = max($data['minimum'], (int) ($data['maximum'] ?? $data['minimum']));

        return $data;
    }

    private function historiser(Request $request, array $data, string $operation, int $fiches): void
    {
        $libelle = AugmentationVues::PERIMETRES[$data['perimetre']][1];
        $libelle .= match ($data['perimetre']) {
            'annuaire_categorie' => ' : '.CategorieAnnuaire::whereKey($data['categorie'])->value('DESIGNATION'),
            'immo_annonceur' => ' : '.User::whereKey($data['annonceur'])->value('name'),
            'fiche' => ' : '.Entreprise::whereKey($data['fiche'])->value('TITRE'),
            default => '',
        };

        DB::table('historique_vues')->insert([
            'user_id' => $request->user()->id,
            'perimetre' => $data['perimetre'],
            'libelle' => mb_substr($libelle, 0, 250),
            'operation' => $operation,
            'minimum' => $data['minimum'],
            'maximum' => $data['maximum'],
            'fiches' => $fiches,
            'created_at' => now(),
        ]);
    }
}
