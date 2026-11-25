<?php

namespace App\Http\Controllers\Immobilier;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Immobilier\Support\Annonceurs;
use App\Http\Controllers\Immobilier\Support\RechercheBiens;
use App\Models\Entreprise;
use App\Models\Immobilier;
use App\Models\Statistique;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnonceurController extends Controller
{
    private const PAGES = [
        'agences' => [
            'titre' => 'Agences immobilières',
            'intro' => 'Les agences qui proposent des biens à la vente ou à la location sur Pratik. Chaque annonce est publiée par l\'agence elle-même : vous traitez directement avec elle.',
        ],
        'promoteurs' => [
            'titre' => 'Promoteurs immobiliers',
            'intro' => 'Programmes neufs, résidences et villas en construction, proposés directement par les promoteurs.',
        ],
        'amenageurs' => [
            'titre' => 'Aménageurs fonciers',
            'intro' => 'Lotissements, terrains viabilisés et parcelles avec documents, proposés par des aménageurs fonciers.',
        ],
    ];

    /** /immobilier/agences, /immobilier/promoteurs, /immobilier/amenageurs-fonciers */
    public function index(string $categorie): View
    {
        return view('immobilier.annonceurs.index', [
            'categorie' => $categorie,
            'page' => self::PAGES[$categorie],
            'annonceurs' => Annonceurs::liste($categorie),
        ]);
    }

    /** /immobilier/annonceur/{lien} : page canonique d'un annonceur et de ses biens. */
    public function show(Request $request, string $lien): View
    {
        $entreprise = Entreprise::where('LIEN', $lien)
            ->whereIn('ID', User::query()->whereNotNull('entreprise_id')->select('entreprise_id'))
            ->with([
                'commune:ID,DESIGNATION',
                'annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1,TEL_1,TEL_2,CEL_1,CEL_2,EMAIL_1,EMAIL_2,WHATSAPP,SITE,ADRESSE,DESCRIPTION,RESUME,FACEBOOK,INSTAGRAM,LINKEDIN',
            ])
            ->firstOrFail(['ID', 'TITRE', 'SIGLE', 'LIEN', 'COMMUNE_ID', 'COMPTEUR', 'VUES_BONUS', 'ACTIVITE']);

        $userIds = User::where('entreprise_id', $entreprise->ID)->pluck('id');

        $transaction = in_array($request->query('transaction'), ['vente', 'location'], true) ? $request->query('transaction') : null;

        $biens = RechercheBiens::requete([], $transaction)
            ->whereIn('immobilier.UTILISATEUR', $userIds)
            ->paginate(12)
            ->withQueryString();

        $compteurs = Immobilier::publie()
            ->whereIn('UTILISATEUR', $userIds)
            ->selectRaw('SUM(TYPE_IMMOBILIER_ID = 1) as locations, SUM(TYPE_IMMOBILIER_ID <> 1) as ventes')
            ->first();

        if ((int) $request->user()?->entreprise_id !== (int) $entreprise->ID) {
            $entreprise->incrementerVues();
            Statistique::enregistrer($entreprise->ID, Statistique::VUE);
        }

        return view('immobilier.annonceurs.show', [
            'entreprise' => $entreprise,
            'annuaire' => $entreprise->annuaire,
            'logo' => Annonceurs::logoUrl($entreprise->annuaire?->LOGO ?: $entreprise->annuaire?->LOGO1),
            'biens' => $biens,
            'transaction' => $transaction,
            'compteurs' => ['ventes' => (int) $compteurs?->ventes, 'locations' => (int) $compteurs?->locations],
            'marqueurs' => RechercheBiens::marqueurs($biens),
        ]);
    }

    /** Ancienne URL /immobilier/agence-{id} : id = entreprise de l'agence. */
    public function legacyEntreprise(int $id): RedirectResponse
    {
        $lien = Entreprise::whereKey($id)->value('LIEN');
        abort_unless($lien, 404);

        return redirect()->route('immo.annonceur', $lien, 301);
    }

    /** Anciennes URLs /immobilier-agence/{id} et /immobilier-promoteur/{id} : id = compte utilisateur. */
    public function legacyUtilisateur(int $id): RedirectResponse
    {
        $lien = Entreprise::whereKey(User::whereKey($id)->value('entreprise_id'))->value('LIEN');
        abort_unless($lien, 404);

        return redirect()->route('immo.annonceur', $lien, 301);
    }

    /** Ancienne URL /immobilier/promoteurs-{lien} : lien = entreprise du promoteur. */
    public function legacyLien(string $lien): RedirectResponse
    {
        return redirect()->route('immo.annonceur', $lien, 301);
    }
}
